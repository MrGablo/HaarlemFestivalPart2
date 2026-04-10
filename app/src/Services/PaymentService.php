<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Utils\QrGenerator;

/**
 * Stripe Checkout session creation and fulfilment (success URL + webhook).
 */
class PaymentService
{
    private PaymentRepository $repo;
    private TicketService $ticketService;
    private EmailService $emailService;
    private TicketPdfGenerator $ticketPdfGenerator;
    private OrderService $orderService;

    public function __construct(
        PaymentRepository $repo,
        ?TicketService $ticketService = null,
        ?EmailService $emailService = null,
        ?TicketPdfGenerator $ticketPdfGenerator = null,
        ?OrderService $orderService = null
    )
    {
        $this->repo = $repo;
        $this->ticketService = $ticketService ?? new TicketService();
        $this->emailService = $emailService ?? new EmailService();
        $this->ticketPdfGenerator = $ticketPdfGenerator ?? new TicketPdfGenerator();
        $this->orderService = $orderService ?? new OrderService(new OrderRepository(), new EventModelBuilderService());
    }

    /**
     * Create Stripe hosted checkout URL for the full pending cart.
     */
    public function createCheckoutUrlForPendingCart(int $userId): string
    {
        \Stripe\Stripe::setApiKey($this->getStripeSecretKey());

        $this->orderService->expireStaleCheckoutDeadlines();

        $pendingOrder = $this->orderService->getPayablePendingOrderForUser($userId);
        if ($pendingOrder === null) {
            throw new \RuntimeException('No pending cart found.');
        }
        $pendingOrderId = (int)($pendingOrder->order_id ?? 0);
        if ($pendingOrderId <= 0) {
            throw new \RuntimeException('Invalid pending cart.');
        }

        $this->orderService->markCheckoutStartedIfNeeded($userId, $pendingOrderId);
        $pendingOrder = $this->orderService->getPayablePendingOrderForUser($userId);
        if ($pendingOrder === null || (int)($pendingOrder->order_id ?? 0) !== $pendingOrderId) {
            throw new \RuntimeException('Pending cart is no longer payable.');
        }

        $items = $pendingOrder->items;
        if ($items === []) {
            throw new \RuntimeException('Pending cart is empty.');
        }

        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = $this->buildStripeLineItem($item);
        }

        $baseUrl = $this->resolveBaseUrl();
        $expiresAt = $this->resolveCheckoutSessionExpiresAt($pendingOrder->payment_deadline_at);

        $session = \Stripe\Checkout\Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'automatic_tax' => [
                'enabled' => true,
            ],
            'expires_at' => $expiresAt,
            'success_url' => $baseUrl . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $baseUrl . '/payment/cancel',
            'metadata' => [
                'user_id' => (string)$userId,
                'pending_order_id' => (string)$pendingOrderId,
            ],
        ]);

        return (string)$session->url;
    }

    /**
     * Marks the pending order paid, creates tickets, sends delivery email.
     */
    public function fulfillPendingOrder(int $userId, int $orderId): void
    {
        if ($this->repo->isOrderPaid($orderId)) {
            return;
        }

        $marked = $this->repo->markOrderAsPaid($orderId, $userId);
        if (!$marked) {
            if ($this->repo->isOrderPaid($orderId)) {
                return;
            }
            throw new \RuntimeException('Payment: could not mark order ' . $orderId . ' as paid.');
        }

        $items = $this->repo->getOrderItemsByOrderId($orderId);
        foreach ($items as $item) {
            $this->createTicketsFromPaidOrderItem($item, $userId);
        }

        $this->sendTicketDeliveryEmail($orderId, $userId);
    }

    /**
     * Retrieves a checkout session from Stripe and fulfils only when paid.
     */
    public function fulfillPaidCheckoutSessionById(string $sessionId): void
    {
        $sessionId = trim($sessionId);
        if ($sessionId === '') {
            throw new \RuntimeException('Payment: missing checkout session id.');
        }

        \Stripe\Stripe::setApiKey($this->getStripeSecretKey());
        $this->handleCheckoutCompleted(\Stripe\Checkout\Session::retrieve($sessionId));
    }

    /**
     * Called by the webhook after Stripe confirms payment.
     */
    public function handleCheckoutCompleted(object $session): void
    {
        $this->orderService->expireStaleCheckoutDeadlines();

        $this->assertSessionIsPaid($session);

        [$userId, $pendingOrderId] = $this->extractSessionMetadata($session);
        if ($userId <= 0 || $pendingOrderId <= 0) {
            throw new \RuntimeException(
                'Payment: checkout session metadata incomplete. user_id=' . $userId . ', pending_order_id=' . $pendingOrderId
            );
        }

        if ($this->repo->isOrderPaid($pendingOrderId)) {
            return;
        }

        if ($this->repo->getOrderDeliveryRecipient($pendingOrderId, $userId) === null) {
            throw new \RuntimeException(
                'Payment: order ' . $pendingOrderId . ' not found for user_id=' . $userId
            );
        }

        $pendingOrder = $this->repo->findPendingOrderByUserId($userId);
        if (!is_array($pendingOrder)) {
            throw new \RuntimeException('Payment: no payable pending order for user_id=' . $userId);
        }

        $pendingOrderIdInDb = (int)($pendingOrder['order_id'] ?? 0);
        if ($pendingOrderIdInDb <= 0 || $pendingOrderIdInDb !== $pendingOrderId) {
            throw new \RuntimeException(
                'Payment: pending order mismatch. metadata=' . $pendingOrderId . ', db=' . $pendingOrderIdInDb
            );
        }

        $this->fulfillPendingOrder($userId, $pendingOrderId);
    }

    private function extractSessionMetadata(object $session): array
    {
        $metadata = $session->metadata ?? null;
        $userId = (int)($metadata->user_id ?? 0);
        $pendingOrderId = (int)($metadata->pending_order_id ?? 0);

        return [$userId, $pendingOrderId];
    }

    private function assertSessionIsPaid(object $session): void
    {
        $status = (string)($session->status ?? '');
        $paymentStatus = (string)($session->payment_status ?? '');

        if ($status !== 'complete' || $paymentStatus !== 'paid') {
            throw new \RuntimeException(
                "Payment: checkout session is not paid. status={$status}, payment_status={$paymentStatus}"
            );
        }
    }

    private function resolveCheckoutSessionExpiresAt(?string $paymentDeadlineAt): int
    {
        $deadline = $paymentDeadlineAt !== null ? trim($paymentDeadlineAt) : '';
        if ($deadline === '') {
            throw new \RuntimeException('Payment: checkout deadline is missing for pending order.');
        }

        $expiresAt = strtotime($deadline);
        if (!is_int($expiresAt) || $expiresAt <= 0) {
            throw new \RuntimeException('Payment: checkout deadline could not be parsed.');
        }

        if ($expiresAt <= time()) {
            throw new \RuntimeException('Payment: checkout deadline has already expired.');
        }

        return $expiresAt;
    }

    private function getStripeSecretKey(): string
    {
        $key = (string)(getenv('STRIPE_SECRET_KEY') ?: ($_ENV['STRIPE_SECRET_KEY'] ?? ($_SERVER['STRIPE_SECRET_KEY'] ?? '')));
        $key = trim($key);
        if ($key === '') {
            throw new \RuntimeException(
                'STRIPE_SECRET_KEY is missing in runtime env. Set it in container env (docker compose env_file) or make sure /app/.env contains it.'
            );
        }

        return $key;
    }

    private function resolveBaseUrl(): string
    {
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $isHttps ? 'https' : 'http';
        $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');

        return $scheme . '://' . $host;
    }

    private function buildStripeLineItem(object $item): array
    {
        $eventId = (int)($item->event_id ?? 0);
        $quantity = (int)($item->quantity ?? 0);
        $priceInCents = (int)round(((float)$item->getUnitPrice()) * 100);
        $eventTitle = (string)($item->event?->title ?? 'Event Ticket');

        if ($eventId <= 0 || $quantity <= 0 || $priceInCents <= 0) {
            throw new \RuntimeException('Invalid pending cart item data.');
        }

        return [
            'price_data' => [
                'currency'     => 'eur',
                'product_data' => [
                    'name' => $eventTitle,
                ],
                'unit_amount' => $priceInCents,
            ],
            'quantity' => $quantity,
        ];
    }

    private function createTicketsFromPaidOrderItem(array $item, int $userId): void
    {
        $orderItemId = (int)($item['order_item_id'] ?? 0);
        $eventId = (int)($item['event_id'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);
        $passDate = isset($item['pass_date']) ? (string)$item['pass_date'] : null;

        if ($orderItemId <= 0 || $eventId <= 0 || $quantity <= 0) {
            return;
        }

        $this->ticketService->createTicketsForOrderItem($orderItemId, $userId, $eventId, $quantity, $passDate);
    }

    private function sendTicketDeliveryEmail(int $orderId, int $userId): void
    {
        $recipient = $this->repo->getOrderDeliveryRecipient($orderId, $userId);
        if (!is_array($recipient)) {
            return;
        }

        $email = trim((string)($recipient['email'] ?? ''));
        if ($email === '') {
            return;
        }

        $tickets = $this->repo->getIssuedTicketsForOrder($orderId);
        if ($tickets === []) {
            return;
        }

        foreach ($tickets as &$ticket) {
            $qr = trim((string)($ticket['qr'] ?? ''));
            $ticket['qr_svg'] = '';

            if ($qr === '') {
                continue;
            }

            try {
                $ticket['qr_svg'] = QrGenerator::generateSvgMarkup($qr);
            } catch (\Throwable $e) {
                error_log('Ticket QR generation failed for order ' . $orderId . ': ' . $e->getMessage());
            }
        }
        unset($ticket);

        $firstName = trim((string)($recipient['first_name'] ?? 'Festival guest'));
        $lastName = trim((string)($recipient['last_name'] ?? ''));
        $orderNumber = sprintf('HF-%06d', $orderId);

        $pdfPath = '';

        try {
            $pdfPath = $this->ticketPdfGenerator->generateTicketsPdf(
                $orderNumber,
                trim($firstName . ' ' . $lastName),
                $tickets
            );

            $sent = $this->emailService->sendTicketDelivery(
                $email,
                $firstName !== '' ? $firstName : 'Festival guest',
                $orderNumber,
                $pdfPath,
                $tickets
            );

            if (!$sent) {
                error_log('Ticket delivery email was not sent for order ' . $orderId);
            }
        } catch (\Throwable $e) {
            error_log('Ticket delivery preparation failed for order ' . $orderId . ': ' . $e->getMessage());
        } finally {
            if ($pdfPath !== '' && is_file($pdfPath)) {
                @unlink($pdfPath);
            }
        }
    }
}
