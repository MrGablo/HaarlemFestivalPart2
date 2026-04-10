<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IReservationRepository;

class ReservationRepository extends Repository implements IReservationRepository
{
    public function createReservation(int $userId, int $yummyEventId, int $adultCount, int $childrenCount, string $note): void
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO Reservation (user_id, yummy_event_id, adult_count, children_count, note, created_at)
            VALUES (:userId, :yummyEventId, :adultCount, :childrenCount, :note, NOW())
        ");
        $stmt->execute([
            ':userId' => $userId,
            ':yummyEventId' => $yummyEventId,
            ':adultCount' => $adultCount,
            ':childrenCount' => $childrenCount,
            ':note' => $note
        ]);
    }
}
