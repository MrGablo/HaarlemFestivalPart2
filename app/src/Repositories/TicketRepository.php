<?php

namespace App\Repositories;

use App\Framework\Repository;
use PDO;

class TicketRepository extends Repository
{
    private ?bool $ticketHasEventIdColumn = null;

    public function createTicket(int $orderItemId, int $userId, int $eventId, string $qr): int
    {
        return $this->createTicketUsingConnection(
            $this->getConnection(),
            $orderItemId,
            $userId,
            $eventId,
            $qr
        );
    }

    public function createTicketUsingConnection(\PDO $connection, int $orderItemId, int $userId, int $eventId, string $qr): int
    {
        if ($this->ticketHasEventIdColumn()) {
            $stmt = $connection->prepare(
                'INSERT INTO `Ticket` (order_item_id, user_id, event_id, qr)
                 VALUES (:order_item_id, :user_id, :event_id, :qr)'
            );
            $stmt->execute([
                ':order_item_id' => $orderItemId,
                ':user_id' => $userId,
                ':event_id' => $eventId,
                ':qr' => $qr,
            ]);
        } else {
            $stmt = $connection->prepare(
                'INSERT INTO `Ticket` (order_item_id, user_id, qr)
                 VALUES (:order_item_id, :user_id, :qr)'
            );
            $stmt->execute([
                ':order_item_id' => $orderItemId,
                ':user_id' => $userId,
                ':qr' => $qr,
            ]);
        }

        return (int)$connection->lastInsertId();
    }

    public function executeInTransaction(callable $callback): void
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $callback($pdo);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function getPaidTicketsForUser(int $userId): array
    {
        $eventExpr = $this->ticketHasEventIdColumn() ? 'COALESCE(t.event_id, oi.event_id)' : 'oi.event_id';

        $stmt = $this->getConnection()->prepare(
            "SELECT
                t.ticket_id,
                t.qr,
                oi.order_item_id,
                oi.order_id,
                {$eventExpr} AS event_id,
                e.title,
                COALESCE(j.price, d.price, p.base_price, 0) AS price,
                '' AS location
             FROM `Ticket` t
             INNER JOIN `order_items` oi ON oi.order_item_id = t.order_item_id
             INNER JOIN `orders` o ON o.order_id = oi.order_id
             INNER JOIN `Event` e ON e.event_id = {$eventExpr}
             LEFT JOIN `JazzEvent` j ON j.event_id = e.event_id
             LEFT JOIN `DanceEvent` d ON d.event_id = e.event_id
             LEFT JOIN `PassEvent` p ON p.event_id = e.event_id
             WHERE o.user_id = :user_id
               AND LOWER(TRIM(o.order_status)) <> :pending_status
                             AND LOWER(TRIM(e.event_type)) <> :pass_type
             ORDER BY t.ticket_id DESC"
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':pending_status' => 'pending',
                        ':pass_type' => 'pass',
        ]);

        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function getTicketInfoByQr(string $qr): ?array
    {
        $eventExpr = $this->ticketHasEventIdColumn() ? 'COALESCE(t.event_id, oi.event_id)' : 'oi.event_id';

        $sql = "
            SELECT
                t.ticket_id,
                t.is_scanned,
                e.title AS event_name,
                e.event_type,
                COALESCE(j.start_date, d.start_date, y.start_time, s.start_date) AS event_start_time
            FROM Ticket t
            JOIN order_items oi ON oi.order_item_id = t.order_item_id
            JOIN Event e ON {$eventExpr} = e.event_id
            LEFT JOIN JazzEvent j ON e.event_id = j.event_id
            LEFT JOIN YummyEvent y ON e.event_id = y.event_id
            LEFT JOIN StoriesEvent s ON e.event_id = s.event_id
            LEFT JOIN DanceEvent d ON e.event_id = d.event_id
            WHERE t.qr = :scanned_qr
            LIMIT 1
        ";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':scanned_qr' => $qr]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function markAsScanned(int $ticketId): void
    {
        $sql = 'UPDATE Ticket SET is_scanned = 1 WHERE ticket_id = :id';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $ticketId]);
    }

    private function ticketHasEventIdColumn(): bool
    {
        if ($this->ticketHasEventIdColumn !== null) {
            return $this->ticketHasEventIdColumn;
        }

        $stmt = $this->getConnection()->query("SHOW COLUMNS FROM `Ticket` LIKE 'event_id'");
        $row = $stmt ? $stmt->fetch() : false;
        $this->ticketHasEventIdColumn = is_array($row);

        return $this->ticketHasEventIdColumn;
    }
}
