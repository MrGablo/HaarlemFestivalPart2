<?php

namespace App\Repositories;

use App\Framework\Repository;
use PDO;

class ScannerRepository extends Repository
{
    public function getTicketInfo(string $qr): ?array
    {
        $sql = "
            SELECT t.ticket_id, t.is_scanned, e.title AS event_name, e.event_type,
            COALESCE(j.start_date, d.start_date, y.start_time, s.start_date) AS event_start_time
            FROM Ticket t
            JOIN order_items oi ON t.order_item_id = oi.order_item_id
            JOIN Event e ON oi.event_id = e.event_id
            LEFT JOIN JazzEvent j ON e.event_id = j.event_id
            LEFT JOIN YummyEvent y ON e.event_id = y.event_id
            LEFT JOIN StoriesEvent s ON e.event_id = s.event_id
            LEFT JOIN DanceEvent d ON e.event_id = d.event_id
            WHERE t.qr = :scanned_qr
        ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':scanned_qr' => $qr]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function markAsScanned(int $ticketId): void
    {
        $sql = "UPDATE Ticket SET is_scanned = 1 WHERE ticket_id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $ticketId]);
    }
}
