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

    public function getJazzEventIdsByDate(string $isoDate): array
    {
        $stmt = $this->getConnection()->prepare(
            "SELECT e.event_id
             FROM Event e
             INNER JOIN JazzEvent j ON j.event_id = e.event_id
             WHERE e.event_type = 'jazz'
               AND DATE(j.start_date) = :pass_date
             ORDER BY j.start_date ASC, e.event_id ASC"
        );
        $stmt->execute([':pass_date' => $isoDate]);
        $rows = $stmt->fetchAll();

        return $this->normalizeEventIds(is_array($rows) ? $rows : []);
    }

    public function getAllJazzEventIds(): array
    {
        $stmt = $this->getConnection()->query(
            "SELECT e.event_id
             FROM Event e
             INNER JOIN JazzEvent j ON j.event_id = e.event_id
             WHERE e.event_type = 'jazz'
             ORDER BY j.start_date ASC, e.event_id ASC"
        );
        $rows = $stmt ? $stmt->fetchAll() : [];

        return $this->normalizeEventIds(is_array($rows) ? $rows : []);
    }

    public function getDanceSessionEventIdsByPassEvent(int $passEventId): array
    {
        try {
            $stmt = $this->getConnection()->prepare(
                "SELECT DATE(COALESCE(d.event_date, d.start_date)) AS day_key
                 FROM DanceEvent d
                 WHERE d.event_id = :event_id
                 LIMIT 1"
            );
            $stmt->execute([':event_id' => $passEventId]);
            $dayKey = $stmt->fetchColumn();

            if (is_string($dayKey) && trim($dayKey) !== '') {
                $eventsStmt = $this->getConnection()->prepare(
                    "SELECT d.event_id
                     FROM DanceEvent d
                     INNER JOIN Event e ON e.event_id = d.event_id
                     WHERE e.event_type = 'dance'
                       AND d.row_kind = 'session'
                       AND DATE(COALESCE(d.event_date, d.start_date)) = :day_key
                     ORDER BY d.sort_order ASC, d.event_id ASC"
                );
                $eventsStmt->execute([':day_key' => trim($dayKey)]);
                $rows = $eventsStmt->fetchAll();

                return $this->normalizeEventIds(is_array($rows) ? $rows : []);
            }
        } catch (\Throwable $e) {
            // Fallback to legacy label schema below.
        }

        $stmt = $this->getConnection()->prepare(
            'SELECT TRIM(d.day_display_label) AS day_label
             FROM DanceEvent d
             WHERE d.event_id = :event_id
             LIMIT 1'
        );
        $stmt->execute([':event_id' => $passEventId]);
        $dayLabel = $stmt->fetchColumn();
        if (!is_string($dayLabel) || trim($dayLabel) === '') {
            return [];
        }

        $eventsStmt = $this->getConnection()->prepare(
            "SELECT d.event_id
             FROM DanceEvent d
             INNER JOIN Event e ON e.event_id = d.event_id
             WHERE e.event_type = 'dance'
               AND d.row_kind = 'session'
               AND TRIM(d.day_display_label) = :day_label
             ORDER BY d.sort_order ASC, d.event_id ASC"
        );
        $eventsStmt->execute([':day_label' => trim((string)$dayLabel)]);
        $rows = $eventsStmt->fetchAll();

        return $this->normalizeEventIds(is_array($rows) ? $rows : []);
    }

    public function getAllDanceSessionEventIds(): array
    {
        $stmt = $this->getConnection()->query(
            "SELECT d.event_id
             FROM DanceEvent d
             INNER JOIN Event e ON e.event_id = d.event_id
             WHERE e.event_type = 'dance'
               AND d.row_kind = 'session'
             ORDER BY d.sort_order ASC, d.event_id ASC"
        );
        $rows = $stmt ? $stmt->fetchAll() : [];

        return $this->normalizeEventIds(is_array($rows) ? $rows : []);
    }

    /** @param array<string, mixed> $row */
    private function normalizePassRow(array $row): PassEvent
    {
        return PassEvent::fromRow($row);
    }

    /** @param array<int, array<string, mixed>> $rows
     *  @return array<int, int>
     */
    private function normalizeEventIds(array $rows): array
    {
        $eventIds = [];
        foreach ($rows as $row) {
            $eventId = (int)($row['event_id'] ?? 0);
            if ($eventId > 0) {
                $eventIds[] = $eventId;
            }
        }

        return array_values(array_unique($eventIds));
    }
}
