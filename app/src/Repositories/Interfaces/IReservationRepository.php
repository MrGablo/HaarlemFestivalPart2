<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

interface IReservationRepository
{
    public function createReservation(int $userId, int $yummyEventId, int $adultCount, int $childrenCount, string $note): void;
}
