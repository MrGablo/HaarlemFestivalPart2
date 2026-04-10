<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Utils\QrGenerator;

/**
 * Handles Stripe checkout session creation and webhook processing.
 * Uses Stripe TEST mode only.
 */
class PaymentService
{
    private PaymentRepository $repo;
    private TicketService $ticketService;
    private EmailService $emailService;
    private TicketPdfGenerator $ticketPdfGenerator;
    private InvoicePdfGenerator $invoicePdfGenerator;
    private OrderService $orderService;

    public function __construct(
        PaymentRepository $repo,
        ?TicketService $ticketService = null,
        ?EmailService $emailService = null,
        ?TicketPdfGenerator $ticketPdfGenerator = null,
        ?InvoicePdfGenerator $invoicePdfGenerator = null,
        ?OrderService $orderService = null
    )
    {
        $this->repo = $repo;
        $this->ticketService = $ticketService ?? new TicketService();
        $this->emailService = $emailService ?? new EmailService();
        $this->ticketPdfGenerator = $ticketPdfGenerator ?? new TicketPdfGenerator();
        $this->invoicePdfGenerator = $invoicePdfGenerator ?? new InvoicePdfGenerator();
        $this->orderService = $orderService ?? new OrderService(new OrderRepository(), new EventModelBuilderService());
    }

    /**
     * Create Stripe hosted checkout URL for the full pending cart.
     */
    public function createCheckoutUrlForPendingCart(int $userId): string
    {
        \Stripe\Stripe::setApiKey($this->getStripeSecretKey());

        $pendingOrder = $this->orderService->getPendingOrderForUser($userId);
        if ($pendingOrder === null) {
            throw new \RuntimeException('No pending cart found.');
        }
        $pendingOrderId = (int)($pendingOrder->order_id ?? 0);
        if ($pendingOrderId <= 0) {
            throw new \RuntimeException('Invalid pending cart.');
        }

        $items = $pendingOrder->items;
        if ($items === []) {
            throw new \RuntimeException('Pending cart is empty.');
        }

        $lineItems = [];
        foreach ($items as $item) {
            $eventId = (int)($item->event_id ?? 0);
            $quantity = (int)($item->quantity ?? 0);
            $priceInCents = (int)round(((float)$item->getUnitPrice()) * 100);
            $eventTitle = (string)($item->event?->title ?? 'Event Ticket');
            if ($eventId <= 0 || $quantity <= 0 || $priceInCents <= 0) {
                throw new \RuntimeException('Invalid pending cart item data.');
            }

            $lineItems[] = [
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

        $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $scheme . '://' . $host;

        $session = \Stripe\Checkout\Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'automatic_tax' => [
                'enabled' => true,
            ],
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
     * Called from the success page using data stored in the PHP session.
     * Marks the pending order as payed and creates Ticket rows.
     */
    public function fulfillPendingOrder(int $userId, int $orderId): void
    {
        $fulfilled = $this->repo->executeInTransaction(function (\PDO $connection) use ($userId, $orderId): bool {
            $order = $this->repo->findLockedOrderByIdForUserUsingConnection($connection, $orderId, $userId);
            if ($order === null) {
                throw new \RuntimeException('Pending order not found for payment fulfilment.');
            }

            $status = strtolower(trim((string)($order['order_status'] ?? '')));
            if ($status === 'payed') {
                return false;
            }

            if ($status !== 'pending') {
                throw new \RuntimeException('Order is not pending and cannot be fulfilled.');
            }

            $items = $this->repo->getOrderItemsByOrderIdUsingConnection($connection, $orderId);
            if ($items === []) {
                throw new \RuntimeException('Cannot fulfil an order without order items.');
            }

            $plan = $this->ticketService->buildFulfillmentPlan($items);
            $this->ticketService->validateAvailabilityForRequirementsUsingConnection($connection, $plan['requirements']);
            $this->ticketService->decrementAvailabilityForRequirementsUsingConnection($connection, $plan['requirements']);
            $this->ticketService->createTicketsForPlansUsingConnection($connection, $userId, $plan['items']);
            $this->repo->markOrderAsPaidUsingConnection($connection, $orderId, $userId);

            return true;
        });

        if (!$fulfilled) {
            return;
        }

        $deliverySent = $this->sendTicketDeliveryEmail($orderId, $userId);

        if (!$deliverySent) {
            error_log("Payment OK but delivery pending: order=$orderId user=$userId");
            return;
        }

        error_log("Payment OK: order=$orderId user=$userId");
    }

    /**
     * Called by the webhook after Stripe confirms payment.
     */
    public function handleCheckoutCompleted(object $session): void
    {
        $metadata = is_object($session->metadata ?? null) ? $session->metadata : null;
        $userId = (int)($metadata->user_id ?? 0);
        $pendingOrderId = (int)($metadata->pending_order_id ?? 0);

        if ($userId <= 0) {
            throw new \RuntimeException('Payment: invalid metadata - user_id=' . $userId);
        }

        if ($pendingOrderId <= 0) {
            throw new \RuntimeException('Payment: missing pending_order_id in checkout session metadata.');
        }

        $this->fulfillPendingOrder($userId, $pendingOrderId);
    }

    public function getOrderStatusForUser(int $userId, int $orderId): ?string
    {
        if ($userId <= 0 || $orderId <= 0) {
            return null;
        }

        return $this->repo->getOrderStatusForUser($orderId, $userId);
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

    private function sendTicketDeliveryEmail(int $orderId, int $userId): bool
    {
        $recipient = $this->repo->getOrderDeliveryRecipient($orderId, $userId);
        if (!is_array($recipient)) {
            return false;
        }

        $email = trim((string)($recipient['email'] ?? ''));
        if ($email === '') {
            return false;
        }

        $tickets = $this->repo->getIssuedTicketsForOrder($orderId);
        if ($tickets === []) {
            return false;
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
        $invoiceItems = $this->repo->getOrderItemsWithPricing($orderId);

        $pdfPath = '';
        $invoicePdfPath = '';

        try {
            $pdfPath = $this->ticketPdfGenerator->generateTicketsPdf(
                $orderNumber,
                trim($firstName . ' ' . $lastName),
                $tickets
            );

            $invoicePdfPath = $this->invoicePdfGenerator->generateInvoicePdf(
                $orderNumber,
                trim($firstName . ' ' . $lastName),
                $invoiceItems,
                isset($recipient['created_at']) ? (string)$recipient['created_at'] : null
            );

            $sent = $this->emailService->sendTicketDelivery(
                $email,
                $firstName !== '' ? $firstName : 'Festival guest',
                $orderNumber,
                $pdfPath,
                $tickets,
                $invoicePdfPath
            );

            if (!$sent) {
                error_log('Ticket delivery email was not sent for order ' . $orderId);
            }

            return $sent;
        } catch (\Throwable $e) {
            error_log('Ticket delivery preparation failed for order ' . $orderId . ': ' . $e->getMessage());
        } finally {
            if ($pdfPath !== '' && is_file($pdfPath)) {
                @unlink($pdfPath);
            }

            if ($invoicePdfPath !== '' && is_file($invoicePdfPath)) {
                @unlink($invoicePdfPath);
            }
        }

        return false;
    }
}
