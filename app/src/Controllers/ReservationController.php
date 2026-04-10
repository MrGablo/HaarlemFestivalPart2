<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\OrderRepository;
use App\Services\EventModelBuilderService;
use App\Services\OrderService;
use App\Services\ReservationService;
use App\Utils\AuthSessionData;
use App\Utils\Flash;
use App\Utils\Session;

class ReservationController
{
    private ReservationService $reservationService;
    private OrderService $orderService;

    public function __construct()
    {
        $this->reservationService = new ReservationService();
        $this->orderService = new OrderService(new OrderRepository(), new EventModelBuilderService());
    }

    public function book(): void
    {
        Session::ensureStarted();

        $auth = AuthSessionData::read();
        if (!is_array($auth)) {
            Flash::setErrors(['login' => 'You must be logged in to reserve a session.']);
            $this->redirect('/login');
        }

        $userId = (int)($auth['userId'] ?? 0);
        if ($userId <= 0) {
            $this->redirect('/login');
        }

        $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
        $yummyEventId = isset($_POST['yummy_event_id']) ? (int)$_POST['yummy_event_id'] : 0;
        $adultCount = isset($_POST['adult_count']) ? (int)$_POST['adult_count'] : 0;
        $childCount = isset($_POST['child_count']) ? (int)$_POST['child_count'] : 0;
        $note = isset($_POST['note']) ? trim((string)$_POST['note']) : '';

        $totalQuantity = $adultCount + $childCount;

        if ($eventId <= 0 || $yummyEventId <= 0 || $totalQuantity <= 0) {
            Flash::setErrors(['reservation' => 'Invalid reservation details provided.']);
            $this->redirect('/yummy');
        }

        try {
            $this->reservationService->createReservation($userId, $yummyEventId, $adultCount, $childCount, $note);
            $this->orderService->addEventToUserPendingOrder($userId, $eventId, $totalQuantity);

            Flash::setSuccess('Reservation placed successfully! You can finalize your bookings in your program/cart.');
            $this->redirect('/yummy/restaurant?id=' . $eventId);
        } catch (\Throwable $e) {
            error_log('Reservation error: ' . $e->getMessage());
            Flash::setErrors(['reservation' => 'Could not process your reservation. Please try again.']);
            $this->redirect('/yummy/restaurant?id=' . $eventId);
        }
    }

    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
