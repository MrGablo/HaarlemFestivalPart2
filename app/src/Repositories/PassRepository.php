<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\PassEvent;
use App\Repositories\Interfaces\IPassRepository;

class PassRepository extends Repository implements IPassRepository
{
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

    /** @param array<string, mixed> $row */
    private function normalizePassRow(array $row): PassEvent
    {
        return PassEvent::fromRow($row);
    }
}
