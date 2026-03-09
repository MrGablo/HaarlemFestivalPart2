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
            Flash::setErrors(['general' => 'Please log in to add tickets to your cart.']);
            header('Location: /login', true, 302);
            exit;
        }

        $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

        try {
            $this->orderService->addEventToUserPendingOrder($userId, $eventId);
            Flash::setSuccess('Ticket added to cart.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        $this->redirectBack('/jazz');
    }

    public function removeItem(): void
    {
        Session::ensureStarted();

        $userId = $this->currentUserId();
        if ($userId === null) {
            Flash::setErrors(['general' => 'Please log in to edit your cart.']);
            header('Location: /login', true, 302);
            exit;
        }

        $orderItemId = isset($_POST['order_item_id']) ? (int)$_POST['order_item_id'] : 0;

        try {
            $this->orderService->removeItemFromPendingOrder($userId, $orderItemId);
            Flash::setSuccess('Item removed from cart.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
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
}
