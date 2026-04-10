<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\PassEvent;
use App\Repositories\Interfaces\IPassRepository;

class PassRepository extends Repository implements IPassRepository
{
    // Pass products are availability-agnostic; covered events are decremented instead.
    private const PASS_EVENT_AVAILABILITY_SENTINEL = 1;

    public function getActivePassProductsByFestivalType(string $festivalType): array
    {
        $festivalType = strtolower(trim($festivalType));
        if ($festivalType === '') {
            return [];
        }

        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            "SELECT
                p.event_id,
                p.festival_type,
                p.pass_scope,
                p.base_price,
                p.active,
                e.title
             FROM PassEvent p
             INNER JOIN Event e ON e.event_id = p.event_id
             WHERE p.active = 1
               AND LOWER(p.festival_type) = :festival_type
               AND e.event_type = 'pass'
             ORDER BY
               CASE p.pass_scope
                   WHEN 'day' THEN 1
                   WHEN 'all_days' THEN 2
                   ELSE 99
               END,
               p.base_price ASC,
               p.event_id ASC"
        );

        $stmt->execute([':festival_type' => $festivalType]);
        $rows = $stmt->fetchAll();

        if (!is_array($rows)) {
            return [];
        }

        return array_values(array_map(function (array $row): PassEvent {
            return $this->normalizePassRow($row);
        }, $rows));
    }

    public function findActivePassProductByEventId(int $eventId): ?PassEvent
    {
        if ($eventId <= 0) {
            return null;
        }

        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            "SELECT
                p.event_id,
                p.festival_type,
                p.pass_scope,
                p.base_price,
                p.active,
                e.title
             FROM PassEvent p
             INNER JOIN Event e ON e.event_id = p.event_id
             WHERE p.active = 1
               AND p.event_id = :event_id
               AND e.event_type = 'pass'
             LIMIT 1"
        );

        $stmt->execute([':event_id' => $eventId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return $this->normalizePassRow($row);
    }

    public function getAvailableJazzPassDates(): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->query(
            "SELECT DISTINCT DATE(j.start_date) AS pass_date
             FROM JazzEvent j
             INNER JOIN Event e ON e.event_id = j.event_id
             WHERE e.event_type = 'jazz'
               AND e.availability > 0
             ORDER BY DATE(j.start_date) ASC"
        );

        $rows = $stmt ? $stmt->fetchAll() : [];
        if (!is_array($rows)) {
            return [];
        }

        $dates = [];
        foreach ($rows as $row) {
            $date = trim((string)($row['pass_date'] ?? ''));
            if ($date !== '') {
                $dates[] = $date;
            }
        }

        return array_values(array_unique($dates));
    }

    public function getAvailableDancePassDates(): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->query(
            "SELECT DISTINCT DATE(COALESCE(d.event_date, d.start_date)) AS pass_date
             FROM DanceEvent d
             INNER JOIN Event e ON e.event_id = d.event_id
             WHERE e.event_type = 'dance'
               AND d.row_kind = 'session'
               AND e.availability > 0
             ORDER BY DATE(COALESCE(d.event_date, d.start_date)) ASC"
        );

        $rows = $stmt ? $stmt->fetchAll() : [];
        if (!is_array($rows)) {
            return [];
        }

        $dates = [];
        foreach ($rows as $row) {
            $date = trim((string)($row['pass_date'] ?? ''));
            if ($date !== '') {
                $dates[] = $date;
            }
        }

        return array_values(array_unique($dates));
    }

    public function getAllPassProducts(): array
    {
        $stmt = $this->getConnection()->query(
            "SELECT
                p.event_id,
                p.festival_type,
                p.pass_scope,
                p.base_price,
                p.active,
                p.created_at,
                p.updated_at,
                e.title
             FROM PassEvent p
             INNER JOIN Event e ON e.event_id = p.event_id
             WHERE e.event_type = 'pass'
             ORDER BY p.festival_type ASC, p.pass_scope ASC, e.title ASC, p.event_id ASC"
        );

        $rows = $stmt ? $stmt->fetchAll() : [];
        return is_array($rows) ? $rows : [];
    }

    public function findPassProductByEventId(int $eventId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            "SELECT
                p.event_id,
                p.festival_type,
                p.pass_scope,
                p.base_price,
                p.active,
                p.created_at,
                p.updated_at,
                e.title,
                e.event_type
             FROM PassEvent p
             INNER JOIN Event e ON e.event_id = p.event_id
             WHERE p.event_id = :event_id
               AND e.event_type = 'pass'
             LIMIT 1"
        );
        $stmt->execute([':event_id' => $eventId]);

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function createPassProduct(string $title, string $festivalType, string $passScope, float $basePrice, bool $active): int
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $insertEvent = $pdo->prepare(
                "INSERT INTO Event (title, event_type, availability)
                 VALUES (:title, 'pass', :availability)"
            );
            $insertEvent->execute([
                ':title' => $title,
                ':availability' => self::PASS_EVENT_AVAILABILITY_SENTINEL,
            ]);

            $eventId = (int)$pdo->lastInsertId();
            if ($eventId <= 0) {
                throw new \RuntimeException('Unable to create pass event.');
            }

            $insertPass = $pdo->prepare(
                'INSERT INTO PassEvent (event_id, festival_type, pass_scope, base_price, active)
                 VALUES (:event_id, :festival_type, :pass_scope, :base_price, :active)'
            );
            $insertPass->execute([
                ':event_id' => $eventId,
                ':festival_type' => $festivalType,
                ':pass_scope' => $passScope,
                ':base_price' => $basePrice,
                ':active' => $active ? 1 : 0,
            ]);

            $pdo->commit();
            return $eventId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updatePassProduct(int $eventId, string $title, string $festivalType, string $passScope, float $basePrice, bool $active): void
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $updateEvent = $pdo->prepare(
                "UPDATE Event
                 SET title = :title
                 WHERE event_id = :event_id
                   AND event_type = 'pass'"
            );
            $updateEvent->execute([
                ':title' => $title,
                ':event_id' => $eventId,
            ]);

            $updatePass = $pdo->prepare(
                'UPDATE PassEvent
                 SET festival_type = :festival_type,
                     pass_scope = :pass_scope,
                     base_price = :base_price,
                     active = :active
                 WHERE event_id = :event_id'
            );
            $updatePass->execute([
                ':festival_type' => $festivalType,
                ':pass_scope' => $passScope,
                ':base_price' => $basePrice,
                ':active' => $active ? 1 : 0,
                ':event_id' => $eventId,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function deletePassProductByEventId(int $eventId): bool
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $inUse = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE event_id = :event_id');
            $inUse->execute([':event_id' => $eventId]);
            if ((int)$inUse->fetchColumn() > 0) {
                throw new \RuntimeException('This pass is used in orders and cannot be deleted. Disable it instead.');
            }

            $deletePass = $pdo->prepare('DELETE FROM PassEvent WHERE event_id = :event_id');
            $deletePass->execute([':event_id' => $eventId]);

            $deleteEvent = $pdo->prepare("DELETE FROM Event WHERE event_id = :event_id AND event_type = 'pass'");
            $deleteEvent->execute([':event_id' => $eventId]);

            $pdo->commit();
            return $deleteEvent->rowCount() > 0;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** @param array<string, mixed> $row */
    private function normalizePassRow(array $row): PassEvent
    {
        return PassEvent::fromRow($row);
    }
}
