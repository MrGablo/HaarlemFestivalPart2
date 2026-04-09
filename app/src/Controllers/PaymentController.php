<?php

namespace App\Controllers;

use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use App\Utils\AuthSessionData;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

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
            Csrf::assertPost('payment_csrf_token');

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

            $checkoutUrl = $this->paymentService->createCheckoutUrlForPendingCart($userId);

            header('HTTP/1.1 303 See Other');
            header('Location: ' . $checkoutUrl);
            exit;
        } catch (\Throwable $e) {
            error_log('Checkout redirect failed: ' . $e->getMessage());
            Flash::setErrors(['general' => 'Unable to start checkout. Open My Program and try again.']);
            header('Location: /program', true, 302);
            exit;
        }
    }

    public function handleWebhook(): void
    {
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret = trim((string)(getenv('STRIPE_WEBHOOK_SECRET') ?: ''));

        if ($payload === false) {
            http_response_code(400);
            echo 'Invalid payload';
            exit;
        }

        if ($secret === '') {
            error_log('STRIPE_WEBHOOK_SECRET is not set.');
            http_response_code(500);
            echo 'Webhook configuration error';
            exit;
        }

        if ($sigHeader === '') {
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

        if ($event->type === 'checkout.session.completed') {
            try {
                $session = $event->data->object;
                $this->paymentService->handleCheckoutCompleted($session);
            } catch (\Throwable $e) {
                error_log('Webhook fulfilment failed: ' . $e->getMessage());
                http_response_code(500);
                echo 'Processing failed';
                exit;
            }
        }

        http_response_code(200);
        echo 'OK';
        exit;
    }

    public function success(): void
    {
        Session::ensureStarted();

        $sessionId = isset($_GET['session_id']) ? trim((string)$_GET['session_id']) : '';
        if ($sessionId !== '') {
            try {
                $this->paymentService->fulfillPaidCheckoutSessionById($sessionId);
            } catch (\Throwable $e) {
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
}
