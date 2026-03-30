<?php

namespace App\Controllers;

use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use App\Utils\AuthSessionData;
use App\Utils\Session;

/**
 * Handles Stripe payment endpoints:
 *  - POST /payment/checkout     (redirect to Stripe hosted Checkout)
 *  - POST /api/payment/webhook  (called by Stripe)
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
    // POST /payment/checkout
    // Server-side redirect to Stripe hosted Checkout prebuilt UI.
    // ---------------------------------------------------------------
    public function checkoutRedirect(): void
    {
        Session::ensureStarted();

        $auth = AuthSessionData::read();
        if (!is_array($auth)) {
            header('Location: /login', true, 302);
            exit;
        }

        $userId = (int)($auth['userId'] ?? 0);
        if ($userId <= 0) {
            header('Location: /program', true, 302);
            exit;
        }

        try {
            $repo = new PaymentRepository();
            $pendingOrder = $repo->findPendingOrderByUserId($userId);
            $pendingOrderId = (int)($pendingOrder['order_id'] ?? 0);

            $checkoutUrl = $this->paymentService->createCheckoutUrlForPendingCart($userId);

            $_SESSION['pending_payment'] = [
                'user_id'  => $userId,
                'order_id' => $pendingOrderId,
            ];
        } catch (\Throwable $e) {
            error_log('Stripe checkout redirect failed: ' . $e->getMessage());
            header('Location: /payment/cancel', true, 302);
            exit;
        }

        header('HTTP/1.1 303 See Other');
        header('Location: ' . $checkoutUrl);
        exit;
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

        // Ensure we actually received a payload
        if ($payload === false) {
            error_log('Stripe webhook error: failed to read request body.');
            http_response_code(400);
            echo 'Invalid payload';
            exit;
        }

        // Ensure the webhook secret is configured
        if ($secret === '') {
            error_log('Stripe webhook configuration error: STRIPE_WEBHOOK_SECRET is not set.');
            http_response_code(500);
            echo 'Webhook configuration error';
            exit;
        }

        // Require a signature header
        if ($sigHeader === '') {
            error_log('Stripe webhook error: missing Stripe signature header.');
            http_response_code(400);
            echo 'Missing signature';
            exit;
        }

        try {
            $event = \Stripe\Webhook::constructEvent((string)$payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            error_log('Webhook signature verification failed: ' . $e->getMessage());
            http_response_code(400);
            echo 'Invalid signature';
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
    // Retrieves the Stripe session and fulfils the order directly
    // (webhooks cannot reach localhost in dev).
    // ---------------------------------------------------------------
    public function success(): void
    {
        Session::ensureStarted();

        $pendingPayment = $_SESSION['pending_payment'] ?? null;
        if (is_array($pendingPayment)) {
            unset($_SESSION['pending_payment']);

            $userId  = (int)($pendingPayment['user_id']  ?? 0);
            $orderId = (int)($pendingPayment['order_id'] ?? 0);

            if ($userId > 0 && $orderId > 0) {
                try {
                    $this->paymentService->fulfillPendingOrder($userId, $orderId);
                } catch (\Throwable $e) {
                    error_log('Payment success fulfilment error: ' . $e->getMessage());
                }
            }
        }

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
}
