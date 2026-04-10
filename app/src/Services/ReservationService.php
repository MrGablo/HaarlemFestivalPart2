<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Interfaces\IReservationRepository;
use App\Repositories\ReservationRepository;

class ReservationService
{
    private IReservationRepository $reservationRepository;

    public function __construct()
    {
        $this->reservationRepository = new ReservationRepository();
    }

    public function createReservation(int $userId, int $yummyEventId, int $adultCount, int $childrenCount, string $note): void
    {
        $this->reservationRepository->createReservation($userId, $yummyEventId, $adultCount, $childrenCount, $note);
    }
}
