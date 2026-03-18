<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Support\VenueSchemaHelper;

/**
 * Dance homepage: timetable and lineup from {@see DanceEvent}, {@see Event}, {@see Venue}.
 */
final class DanceHomeRepository extends Repository
{
    /**
     * All dance timetable rows for the home page (sessions, day passes, all-access).
     *
     * @return list<array<string, mixed>>
     */
    public function findDanceTimetableRows(): array
    {
        $pdo = $this->getConnection();
        $vpk = VenueSchemaHelper::primaryKeyColumn($pdo);
        $locExpr = VenueSchemaHelper::displayNameExpression($pdo, 'v');

        $sql = 'SELECT e.event_id,
                       e.title,
                       d.venue_id,
                       ' . $locExpr . ' AS location_name,
                       d.price,
                       d.session_start,
                       d.session_end,
                       d.session_tag,
                       d.tag_special,
                       d.day_display_label,
                       d.row_kind,
                       d.sort_order
                  FROM DanceEvent d
                  INNER JOIN Event e ON e.event_id = d.event_id AND e.event_type = \'dance\'
                  LEFT JOIN `' . str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo)) . '` v ON v.`' . $vpk . '` = d.venue_id
                 ORDER BY d.sort_order ASC, e.event_id ASC';

        try {
            $stmt = $pdo->query($sql);
            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log('DanceHomeRepository::findDanceTimetableRows: ' . $e->getMessage());

            return [];
        }

        return is_array($rows) ? $rows : [];
    }

    /**
     * Headline acts for the lineup grid: distinct session titles from DB (Event.title), by timetable order.
     *
     * @return list<array{title: string, sort_order: int}>
     */
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
}
