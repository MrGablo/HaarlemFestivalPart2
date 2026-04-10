<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Repositories\OrderRepository;
use App\Repositories\ReservationRepository;
use App\Services\EventModelBuilderService;
use App\Services\OrderService;
use App\Services\TicketService;
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
                $lineTotal = $item->getTotalPrice();
                $subtotal += $lineTotal;
                $unpaidEvents[] = [
                    'orderId' => $pendingOrder->order_id,
                    'orderItemId' => (int)$item->order_item_id,
                    'orderStatus' => $pendingOrder->order_status->value,
                    'title' => (string)($item->event?->title ?? 'Event'),
                    'location' => (string)$item->getLocation(),
                    'quantity' => $qty,
                    'unitPrice' => $unitPrice,
                    'totalPrice' => $lineTotal,
                    'eventId' => (int)($item->event?->event_id ?? 0),
                ];
            }
        }

        $ordersRows = $orderRepo->findOrdersByUserId($userId);
        $orderIds = array_map(static fn(array $row) => (int)($row['order_id'] ?? 0), $ordersRows);
        $orderIds = array_values(array_filter($orderIds, static fn(int $id) => $id > 0));

        $ticketService = new TicketService();
        $reservationRepository = new ReservationRepository();

        $paidEvents = [];
        $totalEvents = 0;
        $reservations = $reservationRepository->getReservationsForUser($userId);

        if ($orderIds !== []) {
            $ticketRows = $ticketService->getPaidTicketsForUser($userId);

            foreach ($ticketRows as $row) {
                $totalEvents += 1;

                $price = (float)($row['price'] ?? 0);
                $paidEvents[] = [
                    'orderId' => (int)($row['order_id'] ?? 0),
                    'orderItemId' => (int)($row['order_item_id'] ?? 0),
                    'orderStatus' => 'payed',
                    'title' => (string)($row['title'] ?? 'Event'),
                    'location' => (string)($row['location'] ?? ''),
                    'quantity' => 1,
                    'unitPrice' => $price,
                    'totalPrice' => $price,
                    'eventId' => (int)($row['event_id'] ?? 0),
                    'ticketQr' => (string)($row['qr'] ?? ''),
                    'ticketId' => (int)($row['ticket_id'] ?? 0),
                ];
            }
        }

        foreach ($unpaidEvents as $ev) {
            $totalEvents += max(0, (int)$ev['quantity']);
        }

        $totalEvents += count($reservations);

        $cartCount = count($unpaidEvents);

        require __DIR__ . '/../Views/pages/program.php';
    }
}
