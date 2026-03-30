<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Support\VenueSchemaHelper;

final class DanceHomeRepository extends Repository
{
    /** @return list<array<string, mixed>> */
    public function findDanceTimetableRows(): array
    {
        $pdo = $this->getConnection();
        $vpk = VenueSchemaHelper::primaryKeyColumn($pdo);
        $locExpr = VenueSchemaHelper::displayNameExpression($pdo, 'v');

        $venueTable = str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo));
        $baseSqlPrefix = 'SELECT e.event_id,
                                 e.title,
                                 d.venue_id,
                                 ' . $locExpr . ' AS location_name,
                                 d.price,';
        $baseSqlSuffix = 'd.session_tag,
                          d.tag_special,
                          d.row_kind,
                          d.sort_order
                     FROM DanceEvent d
                     INNER JOIN Event e ON e.event_id = d.event_id AND e.event_type = \'dance\'
                     LEFT JOIN `' . $venueTable . '` v ON v.`' . $vpk . '` = d.venue_id
                    ORDER BY d.sort_order ASC, e.event_id ASC';

        // New schema: start_date/end_date/event_date (datetime/date columns).
        $sqlNew = $baseSqlPrefix . '
                                 d.start_date AS session_start,
                                 d.end_date AS session_end,
                                 d.event_date AS day_display_label,
                                 ' . $baseSqlSuffix;

        // Legacy schema: session_start/session_end/day_display_label (text columns).
        $sqlLegacy = $baseSqlPrefix . '
                                 d.session_start,
                                 d.session_end,
                                 d.day_display_label,
                                 ' . $baseSqlSuffix;

        $rows = $this->tryFetchRows($pdo, $sqlNew);
        if ($rows === null) {
            $rows = $this->tryFetchRows($pdo, $sqlLegacy);
        }
        if ($rows === null) {
            return [];
        }

        return is_array($rows) ? $rows : [];
    }

    /** @return list<array{title: string, sort_order: int}> */
    public function findDanceLineupHeadlines(int $limit = 6): array
    {
        $limit = max(1, min(12, $limit));
        $pdo = $this->getConnection();
        $sql = 'SELECT e.title AS title, MIN(d.sort_order) AS sort_order
                  FROM DanceEvent d
                  INNER JOIN Event e ON e.event_id = d.event_id AND e.event_type = \'dance\'
                 WHERE d.row_kind = \'session\' AND TRIM(e.title) <> \'\'
                 GROUP BY e.title
                 ORDER BY sort_order ASC, e.title ASC
                 LIMIT ' . $limit;

        try {
            $stmt = $pdo->query($sql);
            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log('DanceHomeRepository::findDanceLineupHeadlines: ' . $e->getMessage());

            return [];
        }

        $out = [];
        foreach (is_array($rows) ? $rows : [] as $r) {
            if (!is_array($r) || !isset($r['title'])) {
                continue;
            }
            $out[] = [
                'title' => (string) $r['title'],
                'sort_order' => (int) ($r['sort_order'] ?? 0),
            ];
        }

        return $out;
    }

    /** @return list<array{name: string, sort_order: int}> */
    public function findDanceLineupArtists(int $limit = 6): array
    {
        $limit = max(1, min(12, $limit));
        $pdo = $this->getConnection();
        $sql = 'SELECT a.name AS name, MIN(d.sort_order) AS sort_order
                  FROM DanceEvent d
                  INNER JOIN Event e ON e.event_id = d.event_id AND e.event_type = \'dance\'
                  INNER JOIN Artist a ON LOCATE(UPPER(a.name), UPPER(e.title)) > 0
                 WHERE d.row_kind = \'session\' AND TRIM(a.name) <> \'\'
                 GROUP BY a.name
                 ORDER BY sort_order ASC, a.name ASC
                 LIMIT ' . $limit;

        try {
            $stmt = $pdo->query($sql);
            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log('DanceHomeRepository::findDanceLineupArtists: ' . $e->getMessage());

            return [];
        }

        $out = [];
        foreach (is_array($rows) ? $rows : [] as $r) {
            if (!is_array($r) || !isset($r['name'])) {
                continue;
            }
            $name = trim((string) $r['name']);
            if ($name === '') {
                continue;
            }
            $out[] = [
                'name' => $name,
                'sort_order' => (int) ($r['sort_order'] ?? 0),
            ];
        }

        return $out;
    }

    /** @return array<int, int> */
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

    /** @return array<int, int> */
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

    /** @return list<array<string, mixed>>|null */
    private function tryFetchRows(\PDO $pdo, string $sql): ?array
    {
        try {
            $stmt = $pdo->query($sql);
            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];

            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('DanceHomeRepository::findDanceTimetableRows variant failed: ' . $e->getMessage());

            return null;
        }
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
