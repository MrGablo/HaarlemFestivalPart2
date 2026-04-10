<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;

final class ReservationRepository extends Repository
{
    private ?bool $reservationHasUserIdColumn = null;
    private ?bool $reservationHasCreatedAtColumn = null;

    public function executeInTransaction(callable $callback): mixed
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    public function findSessionForUpdateUsingConnection(\PDO $connection, int $eventId, int $yummyEventId): ?array
    {
        $stmt = $connection->prepare(
            'SELECT
                y.id AS yummy_event_id,
                y.event_id,
                y.start_time,
                y.end_time,
                e.title,
                e.availability
             FROM YummyEvent y
             INNER JOIN Event e ON e.event_id = y.event_id
             WHERE y.id = :yummy_event_id
               AND y.event_id = :event_id
               AND e.event_type = :event_type
             LIMIT 1
             FOR UPDATE'
        );
        $stmt->execute([
            ':yummy_event_id' => $yummyEventId,
            ':event_id' => $eventId,
            ':event_type' => 'yummy',
        ]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function getReservedSeatsForYummyEventUsingConnection(\PDO $connection, int $yummyEventId): int
    {
        $stmt = $connection->prepare(
            'SELECT COALESCE(SUM(adult_count + children_count), 0)
               FROM Reservation
              WHERE yummy_event_id = :yummy_event_id'
        );
        $stmt->execute([':yummy_event_id' => $yummyEventId]);

        return (int)$stmt->fetchColumn();
    }

    public function createReservationUsingConnection(\PDO $connection, int $userId, int $yummyEventId, int $adultCount, int $childrenCount, string $note): int
    {
        $columns = ['yummy_event_id', 'adult_count', 'children_count', 'note'];
        $params = [
            ':yummy_event_id' => $yummyEventId,
            ':adult_count' => $adultCount,
            ':children_count' => $childrenCount,
            ':note' => $note,
        ];
        $placeholders = [':yummy_event_id', ':adult_count', ':children_count', ':note'];

        if ($this->reservationHasUserIdColumn()) {
            $columns[] = 'user_id';
            $placeholders[] = ':user_id';
            $params[':user_id'] = $userId;
        }

        if ($this->reservationHasCreatedAtColumn()) {
            $columns[] = 'created_at';
            $placeholders[] = 'NOW()';
        }

        $sql = 'INSERT INTO Reservation (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $connection->prepare($sql);
        $stmt->execute($params);

        return (int)$connection->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function getReservationsForUser(int $userId): array
    {
        if ($userId <= 0 || !$this->reservationHasUserIdColumn()) {
            return [];
        }

        $createdAtSelect = $this->reservationHasCreatedAtColumn() ? 'r.created_at' : 'NULL AS created_at';

        $stmt = $this->getConnection()->prepare(
            'SELECT
                r.id,
                r.yummy_event_id,
                r.adult_count,
                r.children_count,
                r.note,
                ' . $createdAtSelect . ',
                e.title,
                y.start_time,
                y.end_time
             FROM Reservation r
             INNER JOIN YummyEvent y ON y.id = r.yummy_event_id
             INNER JOIN Event e ON e.event_id = y.event_id
             WHERE r.user_id = :user_id
             ORDER BY COALESCE(r.created_at, y.start_time) DESC, r.id DESC'
        );
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll();

        $reservations = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            if (!is_array($row)) {
                continue;
            }

            $startTime = trim((string)($row['start_time'] ?? ''));
            $endTime = trim((string)($row['end_time'] ?? ''));
            $reservationTime = $this->formatReservationWindow($startTime, $endTime);

            $reservations[] = [
                'id' => (int)($row['id'] ?? 0),
                'title' => (string)($row['title'] ?? 'Restaurant reservation'),
                'adult_count' => (int)($row['adult_count'] ?? 0),
                'children_count' => (int)($row['children_count'] ?? 0),
                'guest_total' => (int)($row['adult_count'] ?? 0) + (int)($row['children_count'] ?? 0),
                'note' => (string)($row['note'] ?? ''),
                'reservation_time' => $reservationTime,
            ];
        }

        return $reservations;
    }

    private function reservationHasUserIdColumn(): bool
    {
        return $this->reservationHasColumn('user_id', $this->reservationHasUserIdColumn);
    }

    private function reservationHasCreatedAtColumn(): bool
    {
        return $this->reservationHasColumn('created_at', $this->reservationHasCreatedAtColumn);
    }

    private function reservationHasColumn(string $column, ?bool &$cache): bool
    {
        if ($cache !== null) {
            return $cache;
        }

        $stmt = $this->getConnection()->prepare(
            'SELECT 1
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table_name
                AND COLUMN_NAME = :column_name
              LIMIT 1'
        );
        $stmt->execute([
            ':table_name' => 'Reservation',
            ':column_name' => $column,
        ]);

        $cache = (bool)$stmt->fetchColumn();

        return $cache;
    }

    private function formatReservationWindow(string $startTime, string $endTime): string
    {
        if ($startTime === '') {
            return '';
        }

        $startStamp = strtotime($startTime);
        $endStamp = $endTime !== '' ? strtotime($endTime) : false;
        if ($startStamp === false) {
            return $startTime;
        }

        $label = date('D j M Y H:i', $startStamp);
        if ($endStamp !== false) {
            $label .= ' - ' . date('H:i', $endStamp);
        }

        return $label;
    }
}