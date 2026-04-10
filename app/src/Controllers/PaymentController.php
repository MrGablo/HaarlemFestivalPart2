<?php

namespace App\Controllers;

use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use App\Utils\AuthSessionData;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

// Handles pay button redirect, success page, cancel, and Stripe webhook.
class PaymentController
{
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService(new PaymentRepository());
    }

    public function checkoutRedirect(): void
    {
        Session::ensureStarted();

        try {
            // Normal path: CSRF ok, user logged in, Stripe returns a checkout URL.
            Csrf::assertPost('payment_csrf_token');
            $userId = $this->requireAuthenticatedUserId();

            $checkoutUrl = $this->paymentService->createCheckoutUrlForPendingCart($userId);
            $this->redirectSeeOther($checkoutUrl);
        } catch (\Throwable $e) {
            // Wrong token, not logged in, empty cart, Stripe down, or missing API key.
            error_log('Checkout redirect failed: ' . $e->getMessage());
            Flash::setErrors(['general' => 'Unable to start checkout. Open My Program and try again.']);
            $this->redirect('/program');
        }
    }

    public function handleWebhook(): void
    {
        $payload = file_get_contents('php://input');

        if ($payload === false) {
            $this->respondAndExit(400, 'Invalid payload');
        }

        $sigHeader = (string)($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
        $secret = trim((string)(getenv('STRIPE_WEBHOOK_SECRET') ?: ''));
        if ($secret === '') {
            error_log('STRIPE_WEBHOOK_SECRET is not set.');
            $this->respondAndExit(500, 'Webhook configuration error');
        }

        if ($sigHeader === '') {
            $this->respondAndExit(400, 'Missing signature');
        }

        try {
            // Proves the request really came from Stripe (uses STRIPE_WEBHOOK_SECRET).
            $event = \Stripe\Webhook::constructEvent((string)$payload, $sigHeader, $secret);
        } catch (\Throwable $e) {
            error_log('Webhook signature verification failed: ' . $e->getMessage());
            $this->respondAndExit(400, 'Invalid signature');
        }

        if ($event->type === 'checkout.session.completed') {
            try {
                // Mark order paid, create tickets, send email (same rules as success URL).
                $session = $event->data->object;
                $this->paymentService->handleCheckoutCompleted($session);
            } catch (\Throwable $e) {
                error_log('Webhook fulfilment failed: ' . $e->getMessage());
                $this->respondAndExit(500, 'Processing failed');
            }
        }

        $this->respondAndExit(200, 'OK');
    }

    public function success(): void
    {
        Session::ensureStarted();

        $sessionId = isset($_GET['session_id']) ? trim((string)$_GET['session_id']) : '';
        if ($sessionId !== '') {
            try {
                // User landed here after Stripe — complete the order if not already done.
                $this->paymentService->fulfillPaidCheckoutSessionById($sessionId);
            } catch (\Throwable $e) {
                // Still show the thank-you page; webhook may finish the order, or support can help.
                error_log('Payment success fulfilment error: ' . $e->getMessage());
            }
        }

        require __DIR__ . '/../Views/pages/payment_success.php';
    }

    public function cancel(): void
    {
        Session::ensureStarted();
        require __DIR__ . '/../Views/pages/payment_cancel.php';
    }

    private function requireAuthenticatedUserId(): int
    {
        $auth = AuthSessionData::read();
        if (!is_array($auth)) {
            $this->redirect('/login');
        }

        $userId = (int)($auth['userId'] ?? 0);
        if ($userId <= 0) {
            $this->redirect('/program');
        }

        return $userId;
    }

    private function redirect(string $url, int $statusCode = 302): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    private function redirectSeeOther(string $url): void
    {
        header('HTTP/1.1 303 See Other');
        header('Location: ' . $url);
        exit;
    }

    private function respondAndExit(int $statusCode, string $body): void
    {
        http_response_code($statusCode);
        echo $body;
        exit;
    }
}
