<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Repositories\OrderRepository;
use App\Services\EventModelBuilderService;
use App\Services\OrderService;
use App\Services\TicketService;
use App\Utils\AuthSessionData;
use App\Utils\Csrf;
use App\Utils\CsrfException;
use App\Utils\Flash;
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

        $orderService->expireStaleCheckoutDeadlines();

        $pendingOrder = $orderService->getPendingOrderForUser($userId);
        $awaitingOrder = $orderService->getAwaitingPaymentOrderForUser($userId);

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

        $awaitingPaymentEvents = [];
        $awaitingSubtotal = 0.0;
        $paymentDeadlineAt = '';
        $awaitingPaymentOrderId = 0;
        if ($awaitingOrder !== null) {
            $awaitingPaymentOrderId = (int)$awaitingOrder->order_id;
            $paymentDeadlineAt = (string)($awaitingOrder->payment_deadline_at ?? '');
            foreach ($awaitingOrder->items as $item) {
                $unitPrice = $item->getUnitPrice();
                $qty = (int)$item->quantity;
                $lineTotal = $item->getTotalPrice();
                $awaitingSubtotal += $lineTotal;
                $awaitingPaymentEvents[] = [
                    'orderId' => $awaitingOrder->order_id,
                    'orderItemId' => (int)$item->order_item_id,
                    'orderStatus' => $awaitingOrder->order_status->value,
                    'title' => (string)($item->event?->title ?? 'Event'),
                    'location' => (string)$item->getLocation(),
                    'quantity' => $qty,
                    'unitPrice' => $unitPrice,
                    'totalPrice' => $lineTotal,
                    'eventId' => (int)($item->event?->event_id ?? 0),
                ];
            }
        }

        $cancelledOrdersDisplay = [];
        foreach ($orderService->getCancelledOrdersWithItemsForUser($userId) as $cancelledOrder) {
            $lines = [];
            foreach ($cancelledOrder->items as $item) {
                $unitPrice = $item->getUnitPrice();
                $qty = (int)$item->quantity;
                $lineTotal = $item->getTotalPrice();
                $lines[] = [
                    'title' => (string)($item->event?->title ?? 'Event'),
                    'location' => (string)$item->getLocation(),
                    'quantity' => $qty,
                    'unitPrice' => $unitPrice,
                    'totalPrice' => $lineTotal,
                ];
            }
            if ($lines !== []) {
                $cancelledOrdersDisplay[] = [
                    'orderId' => $cancelledOrder->order_id,
                    'createdAt' => (string)($cancelledOrder->created_at ?? ''),
                    'lines' => $lines,
                ];
            }
        }

        $ordersRows = $orderRepo->findOrdersByUserId($userId);
        $orderIds = array_map(static fn(array $row) => (int)($row['order_id'] ?? 0), $ordersRows);
        $orderIds = array_values(array_filter($orderIds, static fn(int $id) => $id > 0));

        $ticketService = new TicketService();

        $paidEvents = [];
        $totalEvents = 0;

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
                    'eventStartRaw' => trim((string)($row['event_start_time'] ?? '')),
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
        foreach ($awaitingPaymentEvents as $ev) {
            $totalEvents += max(0, (int)$ev['quantity']);
        }

        $cartCount = count($unpaidEvents);

        require __DIR__ . '/../Views/pages/program.php';
    }

    public function cancelAwaitingPayment(): void
    {
        Session::ensureStarted();

        $auth = AuthSessionData::read();
        if (!is_array($auth)) {
            header('Location: /login', true, 302);
            exit;
        }

        $userId = (int)($auth['userId'] ?? 0);
        if ($userId <= 0) {
            header('Location: /login', true, 302);
            exit;
        }

        try {
            Csrf::assertPost('program_cancel_awaiting_csrf_token');
            $orderId = (int)($_POST['order_id'] ?? 0);

            $orderService = new OrderService(new OrderRepository(), new EventModelBuilderService());
            $orderService->expireStaleCheckoutDeadlines();
            $orderService->cancelAwaitingPaymentOrderForUser($userId, $orderId);

            Flash::setSuccess('Checkout cancelled. You can add new tickets from the event pages.');
        } catch (CsrfException $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /program', true, 302);
        exit;
    }
}
