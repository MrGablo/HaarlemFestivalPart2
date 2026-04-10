<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ReservationRepository;
use App\Services\ReservationService;
use App\Utils\AuthSessionData;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class ReservationController
{
    private ReservationService $reservationService;

    public function __construct()
    {
        $this->reservationService = new ReservationService(new ReservationRepository());
    }

    public function book(): void
    {
        Session::ensureStarted();

        $auth = AuthSessionData::read();
        $userId = is_array($auth) ? (int)($auth['userId'] ?? 0) : 0;
        if ($userId <= 0) {
            Flash::setErrors(['general' => 'Please log in to complete a restaurant reservation.']);
            header('Location: /login', true, 302);
            exit;
        }

        $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
        $yummyEventId = isset($_POST['yummy_event_id']) ? (int)$_POST['yummy_event_id'] : 0;

        Flash::setOld([
            'event_id' => $eventId,
            'yummy_event_id' => $yummyEventId,
            'adult_count' => $_POST['adult_count'] ?? '2',
            'child_count' => $_POST['child_count'] ?? '0',
            'note' => $_POST['note'] ?? '',
        ]);

        try {
            Csrf::assertPost('reservation_csrf_token');

            $this->reservationService->createReservationForUser(
                $userId,
                $eventId,
                $yummyEventId,
                isset($_POST['adult_count']) ? (int)$_POST['adult_count'] : 0,
                isset($_POST['child_count']) ? (int)$_POST['child_count'] : 0,
                (string)($_POST['note'] ?? '')
            );

            Flash::setOld([]);
            Flash::setSuccess('Reservation confirmed. You can find it in My Program.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        $this->redirectToRestaurant($eventId);
    }

    private function redirectToRestaurant(int $eventId): void
    {
        $target = $eventId > 0 ? '/yummy/restaurant?id=' . $eventId : '/yummy';
        header('Location: ' . $target, true, 302);
        exit;
    }
}