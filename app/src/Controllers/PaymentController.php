<?php

namespace App\Controllers;

use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use App\Utils\AuthSessionData;
use App\Utils\Session;

/**
 * Handles Stripe payment endpoints:
 *  - POST /api/payment/create-session  (logged-in user)
 *  - POST /api/payment/webhook         (called by Stripe)
 *  - GET  /payment/success
 *  - GET  /payment/cancel
 */
class PaymentController
{
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService(new PaymentRepository());
    }

    // ---------------------------------------------------------------
    // POST /api/payment/create-session
    // Expects JSON body: { "event_id": 1, "quantity": 1 }
    // Returns JSON:      { "ok": true, "session_id": "cs_test_..." }
    // ---------------------------------------------------------------
    public function createCheckoutSession(): void
    {
        Session::ensureStarted();

        // Must be logged in
        $auth = AuthSessionData::read();
        if (!is_array($auth)) {
            $this->json(['ok' => false, 'message' => 'Please log in first.'], 401);
            return;
        }

        $userId = (int) $auth['userId'];

        // Accept JSON body or regular POST form data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $eventId  = isset($input['event_id']) ? (int) $input['event_id'] : 0;
        $quantity = isset($input['quantity']) ? (int) $input['quantity'] : 1;

        if ($eventId <= 0) {
            $this->json(['ok' => false, 'message' => 'Invalid event_id.'], 400);
            return;
        }
        if ($quantity <= 0) {
            $this->json(['ok' => false, 'message' => 'Invalid quantity.'], 400);
            return;
        }

        try {
            $sessionId = $this->paymentService->createCheckoutSession($userId, $eventId, $quantity);
            $this->json(['ok' => true, 'session_id' => $sessionId]);
        } catch (\Throwable $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ---------------------------------------------------------------
    // POST /api/payment/webhook
    // Called by Stripe servers after payment succeeds.
    // Verifies signature, then creates Order + OrderItem + Ticket.
    // ---------------------------------------------------------------
    public function handleWebhook(): void
    {
        // Read the raw request body (needed for signature check)
        $payload   = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret    = getenv('STRIPE_WEBHOOK_SECRET') ?: '';

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            http_response_code(400);
            echo 'Webhook signature verification failed: ' . $e->getMessage();
            exit;
        }

        // We only care about successful checkouts
        if ($event->type === 'checkout.session.completed') {
            try {
                $session = $event->data->object;
                $this->paymentService->handleCheckoutCompleted($session);
            } catch (\Throwable $e) {
                error_log('Webhook processing failed: ' . $e->getMessage());
                http_response_code(500);
                echo 'Processing failed';
                exit;
            }
        }

        http_response_code(200);
        echo 'OK';
        exit;
    }

    // ---------------------------------------------------------------
    // GET /payment/success
    // ---------------------------------------------------------------
    public function success(): void
    {
        Session::ensureStarted();
        require __DIR__ . '/../Views/pages/payment_success.php';
    }

    // ---------------------------------------------------------------
    // GET /payment/cancel
    // ---------------------------------------------------------------
    public function cancel(): void
    {
        Session::ensureStarted();
        require __DIR__ . '/../Views/pages/payment_cancel.php';
    }

    // ---------------------------------------------------------------
    // Helper: send JSON response and stop
    // ---------------------------------------------------------------
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
