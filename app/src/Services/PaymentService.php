<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Utils\QrGenerator;

// Stripe checkout: build payment links, finish orders after pay, handle webhook.
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

    // Sends the user to Stripe to pay for everything in their pending cart.
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

        try {
            // Network or Stripe account issues show up here.
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
        } catch (\Throwable $e) {
            error_log('Stripe Checkout Session::create failed: ' . $e->getMessage());
            throw $e;
        }

        return (string)$session->url;
    }

    // After payment: mark order paid, create tickets, email the buyer.
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

        // Errors inside are logged there; paid order and tickets stay valid.
        $this->sendTicketDeliveryEmail($orderId, $userId);
    }

    // Loads the Stripe session; if it is paid, completes the order (same as webhook path).
    public function fulfillPaidCheckoutSessionById(string $sessionId): void
    {
        $sessionId = trim($sessionId);
        if ($sessionId === '') {
            throw new \RuntimeException('Payment: missing checkout session id.');
        }

        \Stripe\Stripe::setApiKey($this->getStripeSecretKey());
        try {
            $stripeSession = \Stripe\Checkout\Session::retrieve($sessionId);
        } catch (\Throwable $e) {
            error_log('Stripe Checkout Session::retrieve failed: ' . $e->getMessage());
            throw $e;
        }
        $this->handleCheckoutCompleted($stripeSession);
    }

    // Stripe webhook calls this when checkout is successfully paid.
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
        $host = $this->readValidatedRequestHost();
        if ($host === null) {
            $host = 'localhost';
        }

        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $isHttps ? 'https' : 'http';

        return $scheme . '://' . $host;
    }

    private function readValidatedRequestHost(): ?string
    {
        $rawHost = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
        if ($rawHost === '') {
            return null;
        }

        if (!preg_match('/^[A-Za-z0-9.-]+(?::\d{1,5})?$/', $rawHost)) {
            return null;
        }

        $host = strtolower($rawHost);
        $localHosts = ['localhost', 'localhost:80', 'localhost:443', '127.0.0.1', '127.0.0.1:80', '127.0.0.1:443'];

        return in_array($host, $localHosts, true) ? $rawHost : null;
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
                // Email still sends; QR can be missing for one ticket if the library fails.
                error_log('Ticket QR generation failed for order ' . $orderId . ': ' . $e->getMessage());
            }
        }
        unset($ticket);

        $firstName = trim((string)($recipient['first_name'] ?? 'Festival guest'));
        $lastName = trim((string)($recipient['last_name'] ?? ''));
        $orderNumber = sprintf('HF-%06d', $orderId);

        $pdfPath = '';

        try {
            // Build PDF and send mail; temp file is removed in finally.
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
            // Paid tickets in DB are unchanged; operator can resend from support if needed.
            error_log('Ticket delivery preparation failed for order ' . $orderId . ': ' . $e->getMessage());
        } finally {
            if ($pdfPath !== '' && is_file($pdfPath)) {
                @unlink($pdfPath);
            }
        }
    }
}
