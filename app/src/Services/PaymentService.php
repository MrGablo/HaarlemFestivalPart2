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
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

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
     * Called by the webhook after Stripe confirms payment.
     * Creates Order + OrderItem + Ticket(s) in the database.
     */
    public function handleCheckoutCompleted(object $session): void
    {
        $userId   = (int) ($session->metadata->user_id  ?? 0);
        $eventId  = (int) ($session->metadata->event_id ?? 0);
        $quantity = (int) ($session->metadata->quantity  ?? 1);

        if ($userId <= 0 || $eventId <= 0) {
            throw new \RuntimeException(
                'Payment webhook: invalid metadata - user_id=' . $userId . ', event_id=' . $eventId
            );
        }

        // 1) Create Order with status 'paid'
        $orderId = $this->repo->createPaidOrder($userId);

        // 2) Create OrderItem
        $orderItemId = $this->repo->createOrderItem($orderId, $eventId, $quantity);

        // 3) Create one Ticket per quantity (simple QR string)
        for ($i = 0; $i < $quantity; $i++) {
            $qr = 'TICKET_' . uniqid('', true);
            $this->repo->createTicket($orderItemId, $userId, $qr);
        }

        error_log("Payment OK: order=$orderId user=$userId event=$eventId qty=$quantity");
    }
}
