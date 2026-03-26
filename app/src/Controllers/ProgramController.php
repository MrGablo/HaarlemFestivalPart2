<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Repositories\OrderRepository;
use App\Services\EventModelBuilderService;
use App\Services\OrderService;
use App\Utils\AuthSessionData;
use App\Utils\Session;

class ProgramController
{
    public function show(): void
    {
        Session::ensureStarted();

        $auth = AuthSessionData::read();
        $isLoggedIn = $auth !== null;
        if (!$isLoggedIn) {
            header('Location: /login', true, 302);
            exit;
        }

        $profilePicturePath = $auth['profilePicturePath'] ?? Config::DEFAULT_USER_PROFILE_IMAGE_PATH;
        $currentPage = 'program';
        $userId = (int)($auth['userId'] ?? 0);

        $orderRepo = new OrderRepository();
        $orderService = new OrderService($orderRepo, new EventModelBuilderService());

        $pendingOrder = $orderService->getPendingOrderForUser($userId);

        $unpaidEvents = [];
        $subtotal = 0.0;
        if ($pendingOrder !== null) {
            foreach ($pendingOrder->items as $item) {
                $unitPrice = $item->getUnitPrice();
                $qty = (int)$item->quantity;
                $subtotal += $unitPrice * $qty;
                $unpaidEvents[] = [
                    'orderId' => $pendingOrder->order_id,
                    'orderItemId' => (int)$item->order_item_id,
                    'orderStatus' => $pendingOrder->order_status->value,
                    'title' => (string)($item->event?->title ?? 'Event'),
                    'location' => (string)$item->getLocation(),
                    'quantity' => $qty,
                    'unitPrice' => $unitPrice,
                    'totalPrice' => $unitPrice * $qty,
                    'eventId' => (int)($item->event?->event_id ?? 0),
                ];
            }
        }

        $ordersRows = $orderRepo->findOrdersByUserId($userId);
        $orderIds = array_map(static fn(array $row) => (int)($row['order_id'] ?? 0), $ordersRows);
        $orderIds = array_values(array_filter($orderIds, static fn(int $id) => $id > 0));

        $paidEvents = [];
        $totalEvents = 0;

        if ($orderIds !== []) {
            $itemsRows = $orderRepo->getOrderItemsForOrders($orderIds);

            $statusByOrderId = [];
            foreach ($ordersRows as $row) {
                $oid = (int)($row['order_id'] ?? 0);
                if ($oid > 0) {
                    $statusByOrderId[$oid] = strtolower((string)($row['order_status'] ?? 'pending'));
                }
            }

            foreach ($itemsRows as $row) {
                $oid = (int)($row['order_id'] ?? 0);
                if ($oid <= 0) {
                    continue;
                }

                $status = $statusByOrderId[$oid] ?? 'pending';
                $qty = (int)($row['quantity'] ?? 0);
                $price = (float)($row['price'] ?? 0);

                $isPending = $status === 'pending';
                if ($isPending) {
                    continue;
                }

                $totalEvents += max(0, $qty);

                $paidEvents[] = [
                    'orderId' => $oid,
                    'orderStatus' => $status,
                    'title' => (string)($row['title'] ?? 'Event'),
                    'location' => (string)($row['location'] ?? ''),
                    'quantity' => $qty,
                    'unitPrice' => $price,
                    'totalPrice' => $price * $qty,
                    'eventId' => (int)($row['event_id'] ?? 0),
                ];
            }
        }

        foreach ($unpaidEvents as $ev) {
            $totalEvents += max(0, (int)$ev['quantity']);
        }

        $cartCount = count($unpaidEvents);

        require __DIR__ . '/../Views/pages/program.php';
    }
}
