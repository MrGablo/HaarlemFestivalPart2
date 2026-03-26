<?php

namespace App\Services;

use App\Repositories\PaymentRepository;

/**
 * Handles Stripe checkout session creation and webhook processing.
 * Uses Stripe TEST mode only.
 */
class PaymentService
{
    private PaymentRepository $repo;

    public function __construct(PaymentRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Create a Stripe Checkout Session for the given event.
     * Returns the Stripe session ID so the frontend can redirect.
     */
    public function createCheckoutSession(int $userId, int $eventId, int $quantity): string
    {
        // Set Stripe secret key (TEST mode)
        \Stripe\Stripe::setApiKey($this->getStripeSecretKey());

        // Look up event + price in the database
        $event = $this->repo->findEventById($eventId);
        if ($event === null) {
            throw new \RuntimeException('Event not found.');
        }

        $eventTitle   = $event['title'] ?? 'Event Ticket';
        $priceInCents = (int) (($event['price'] ?? 0) * 100); // Stripe uses cents

        if ($priceInCents <= 0) {
            throw new \RuntimeException('Event has no valid price.');
        }

        // Build base URL for redirect pages
        $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $scheme . '://' . $host;

        // Create the Checkout Session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => [
                        'name' => $eventTitle,
                    ],
                    'unit_amount' => $priceInCents,
                ],
                'quantity' => $quantity,
            ]],
            'mode'        => 'payment',
            'automatic_tax' => [
                'enabled' => true,
            ],
            'success_url' => $baseUrl . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $baseUrl . '/payment/cancel',
            // Store our own data so the webhook knows what to create
            'metadata' => [
                'user_id'  => (string) $userId,
                'event_id' => (string) $eventId,
                'quantity'  => (string) $quantity,
            ],
        ]);

        return $session->id;
    }

    /**
     * Create Stripe hosted checkout URL for the full pending cart.
     * This follows Stripe sample flow: server creates session, client is redirected to session URL.
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
            $quantity    = (int)($item['quantity'] ?? 0);
            if ($orderItemId <= 0 || $quantity <= 0) {
                continue;
            }
            for ($i = 0; $i < $quantity; $i++) {
                try {
                    $qr = 'TICKET_' . uniqid('', true);
                    $this->repo->createTicket($orderItemId, $userId, $qr);
                } catch (\Throwable $e) {
                    error_log("Ticket creation failed: " . $e->getMessage());
                }
            }
        }

        error_log("Payment OK: order=$orderId user=$userId");
    }

    /**
     * Called by the webhook after Stripe confirms payment.
     */
    public function handleCheckoutCompleted(object $session): void
    {
        $userId         = (int) ($session->metadata->user_id          ?? 0);
        $pendingOrderId = (int) ($session->metadata->pending_order_id ?? 0);

        if ($userId <= 0) {
            throw new \RuntimeException('Payment: invalid metadata - user_id=' . $userId);
        }

        if ($pendingOrderId > 0) {
            $this->fulfillPendingOrder($userId, $pendingOrderId);
            return;
        }

        $eventId  = (int) ($session->metadata->event_id ?? 0);
        $quantity = (int) ($session->metadata->quantity  ?? 1);
        if ($eventId <= 0 || $quantity <= 0) {
            throw new \RuntimeException('Payment: invalid fallback item metadata.');
        }

        $orderId     = $this->repo->createPaidOrder($userId);
        $orderItemId = $this->repo->createOrderItem($orderId, $eventId, $quantity);
        for ($i = 0; $i < $quantity; $i++) {
            try {
                $qr = 'TICKET_' . uniqid('', true);
                $this->repo->createTicket($orderItemId, $userId, $qr);
            } catch (\Throwable $e) {
                error_log("Ticket creation failed: " . $e->getMessage());
            }
        }
        error_log("Payment OK (fallback): order=$orderId user=$userId event=$eventId qty=$quantity");
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
}
