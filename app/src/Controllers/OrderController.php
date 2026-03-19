<?php

namespace App\Controllers;

use App\Repositories\OrderRepository;
use App\Services\EventModelBuilderService;
use App\Services\OrderService;
use App\Utils\AuthSessionData;
use App\Utils\Flash;
use App\Utils\Session;

class OrderController
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService(new OrderRepository(), new EventModelBuilderService());
    }

    public function addItem(): void
    {
        Session::ensureStarted();
        $userId = $this->currentUserId();
        if ($userId === null) {
            $message = 'Please log in to add tickets to your cart.';
            Flash::setErrors(['general' => $message]);
            $this->jsonResponse([
                'ok' => false,
                'message' => $message,
                'redirect' => '/login',
            ], 401);
        }

        $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

        try {
            $order = $this->orderService->addEventToUserPendingOrder($userId, $eventId);
            Flash::setSuccess('Ticket added to cart.');
            $this->jsonResponse([
                'ok' => true,
                'message' => 'Ticket added to cart.',
                'cart' => $this->buildCartPayload($order),
            ]);
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            Flash::setErrors(['general' => $message]);
            $this->jsonResponse([
                'ok' => false,
                'message' => $message,
            ], 422);
        }
    }

    public function removeItem(): void
    {
        Session::ensureStarted();

        $userId = $this->currentUserId();
        if ($userId === null) {
            $message = 'Please log in to edit your cart.';
            Flash::setErrors(['general' => $message]);

            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'ok' => false,
                    'message' => $message,
                    'redirect' => '/login',
                ], 401);
            }

            header('Location: /login', true, 302);
            exit;
        }

        $orderItemId = isset($_POST['order_item_id']) ? (int)$_POST['order_item_id'] : 0;

        try {
            $order = $this->orderService->removeItemFromPendingOrder($userId, $orderItemId);
            Flash::setSuccess('Item removed from cart.');

            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Item removed from cart.',
                    'cart' => $this->buildCartPayload($order),
                ]);
            }
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            Flash::setErrors(['general' => $message]);

            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'ok' => false,
                    'message' => $message,
                ], 422);
            }
        }

        $this->redirectBack('/jazz');
    }

    public function updateItemQuantity(): void
    {
        Session::ensureStarted();

        $userId = $this->currentUserId();
        if ($userId === null) {
            $message = 'Please log in to edit your cart.';
            Flash::setErrors(['general' => $message]);

            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'ok' => false,
                    'message' => $message,
                    'redirect' => '/login',
                ], 401);
            }

            header('Location: /login', true, 302);
            exit;
        }

        $orderItemId = isset($_POST['order_item_id']) ? (int)$_POST['order_item_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

        try {
            $order = $this->orderService->updateItemQuantityInPendingOrder($userId, $orderItemId, $quantity);
            Flash::setSuccess('Cart quantity updated.');

            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'ok' => true,
                    'message' => 'Cart quantity updated.',
                    'cart' => $this->buildCartPayload($order),
                ]);
            }
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            Flash::setErrors(['general' => $message]);

            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'ok' => false,
                    'message' => $message,
                ], 422);
            }
        }

        $this->redirectBack('/jazz');
    }

    private function currentUserId(): ?int
    {
        $payload = AuthSessionData::read();
        if (!is_array($payload)) {
            return null;
        }

        $userId = isset($payload['userId']) ? (int)$payload['userId'] : 0;
        return $userId > 0 ? $userId : null;
    }

    private function redirectBack(string $fallback): void
    {
        $referer = isset($_SERVER['HTTP_REFERER']) ? (string)$_SERVER['HTTP_REFERER'] : '';
        $target = $referer !== '' ? $referer : $fallback;

        header('Location: ' . $target, true, 302);
        exit;
    }

    private function isAjaxRequest(): bool
    {
        $requestedWith = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            ? strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH'])
            : '';
        if ($requestedWith === 'xmlhttprequest') {
            return true;
        }

        $accept = isset($_SERVER['HTTP_ACCEPT']) ? strtolower((string)$_SERVER['HTTP_ACCEPT']) : '';
        return str_contains($accept, 'application/json');
    }

    private function jsonResponse(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    private function buildCartPayload(?\App\Models\Order $order): array
    {
        if ($order === null) {
            return [
                'itemCount' => 0,
                'total' => 0,
                'totalLabel' => number_format(0, 2),
                'items' => [],
            ];
        }

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'orderItemId' => (int)$item->order_item_id,
                'title' => (string)($item->event?->title ?? 'Event'),
                'location' => (string)$item->getLocation(),
                'quantity' => (int)$item->quantity,
                'unitPrice' => (float)$item->getUnitPrice(),
                'unitPriceLabel' => number_format($item->getUnitPrice(), 2),
            ];
        }

        return [
            'itemCount' => $order->getItemCount(),
            'total' => $order->getTotalPrice(),
            'totalLabel' => number_format($order->getTotalPrice(), 2),
            'items' => $items,
        ];
    }
}
