<?php

namespace App\Services;

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

    public function __construct(
        PaymentRepository $repo,
        ?TicketService $ticketService = null,
        ?EmailService $emailService = null,
        ?TicketPdfGenerator $ticketPdfGenerator = null
    )
    {
        $this->repo = $repo;
        $this->ticketService = $ticketService ?? new TicketService();
        $this->emailService = $emailService ?? new EmailService();
        $this->ticketPdfGenerator = $ticketPdfGenerator ?? new TicketPdfGenerator();
    }

    /**
     * Create Stripe hosted checkout URL for the full pending cart.
     */
    public function createCheckoutUrlForPendingCart(int $userId): string
    {
        \Stripe\Stripe::setApiKey($this->getStripeSecretKey());

        $pendingOrder = $this->repo->findPendingOrderByUserId($userId);
        if ($pendingOrder === null) {
            throw new \RuntimeException('No pending cart found.');
        }
        $pendingOrderId = (int)($pendingOrder['order_id'] ?? 0);
        if ($pendingOrderId <= 0) {
            throw new \RuntimeException('Invalid pending cart.');
        }

        $items = $this->repo->getPendingOrderItemsWithPricing($pendingOrderId);
        if ($items === []) {
            throw new \RuntimeException('Pending cart is empty.');
        }

        $lineItems = [];
        foreach ($items as $item) {
            $eventId = (int)($item['event_id'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);
            $priceInCents = (int)(((float)($item['price'] ?? 0)) * 100);
            $eventTitle = (string)($item['title'] ?? 'Event Ticket');
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
        if ($this->repo->isOrderPaid($orderId)) {
            return;
        }

        $this->repo->markOrderAsPaid($orderId, $userId);

        $items = $this->repo->getOrderItemsByOrderId($orderId);
        foreach ($items as $item) {
            $orderItemId = (int)($item['order_item_id'] ?? 0);
            $eventId = (int)($item['event_id'] ?? 0);
            $quantity    = (int)($item['quantity'] ?? 0);
            $passDate = isset($item['pass_date']) ? (string)$item['pass_date'] : null;

            if ($orderItemId <= 0 || $eventId <= 0 || $quantity <= 0) {
                continue;
            }

            $this->ticketService->createTicketsForOrderItem($orderItemId, $userId, $eventId, $quantity, $passDate);
        }

        $this->sendTicketDeliveryEmail($orderId, $userId);

        error_log("Payment OK: order=$orderId user=$userId");
    }

    /**
     * Called by the webhook after Stripe confirms payment.
     */
    public function handleCheckoutCompleted(object $session): void
    {
        $userId           = (int) ($session->metadata->user_id ?? 0);
        $pendingOrderId   = (int) ($session->metadata->pending_order_id ?? 0);

        if ($userId <= 0) {
            throw new \RuntimeException('Payment: invalid metadata - user_id=' . $userId);
        }

        if ($pendingOrderId <= 0) {
            throw new \RuntimeException('Payment: missing pending_order_id in checkout session metadata.');
        }

        $this->fulfillPendingOrder($userId, $pendingOrderId);
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
