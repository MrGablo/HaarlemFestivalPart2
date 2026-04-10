<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReservationRepository;

final class ReservationService
{
    public function __construct(
        private ReservationRepository $reservations = new ReservationRepository()
    ) {}

    public function createReservationForUser(int $userId, int $eventId, int $yummyEventId, int $adultCount, int $childrenCount, string $note): int
    {
        if ($userId <= 0) {
            throw new \RuntimeException('You must be signed in to reserve a restaurant booking.');
        }

        if ($eventId <= 0 || $yummyEventId <= 0) {
            throw new \RuntimeException('Invalid restaurant reservation selection.');
        }

        if ($adultCount <= 0) {
            throw new \RuntimeException('At least one adult guest is required for a reservation.');
        }

        if ($childrenCount < 0) {
            throw new \RuntimeException('Child guest count cannot be negative.');
        }

        $guestTotal = $adultCount + $childrenCount;
        if ($guestTotal <= 0) {
            throw new \RuntimeException('Please select at least one guest.');
        }

        if ($guestTotal > 20) {
            throw new \RuntimeException('A single reservation can contain at most 20 guests.');
        }

        $note = trim($note);
        if (mb_strlen($note) > 2000) {
            throw new \RuntimeException('Reservation note must be 2000 characters or fewer.');
        }

        return $this->reservations->executeInTransaction(function (\PDO $connection) use ($userId, $eventId, $yummyEventId, $adultCount, $childrenCount, $guestTotal, $note): int {
            $session = $this->reservations->findSessionForUpdateUsingConnection($connection, $eventId, $yummyEventId);
            if ($session === null) {
                throw new \RuntimeException('The selected restaurant timeslot is no longer available.');
            }

            $capacity = (int)($session['availability'] ?? 0);
            $reservedSeats = $this->reservations->getReservedSeatsForYummyEventUsingConnection($connection, $yummyEventId);
            $remainingSeats = $capacity - $reservedSeats;

            if ($remainingSeats <= 0 || $guestTotal > $remainingSeats) {
                throw new \RuntimeException('Not enough seats remain for that reservation. Please pick a different timeslot or reduce the guest count.');
            }

            return $this->reservations->createReservationUsingConnection(
                $connection,
                $userId,
                $yummyEventId,
                $adultCount,
                $childrenCount,
                $note
            );
        });
    }
}