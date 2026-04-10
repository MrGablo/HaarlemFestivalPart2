<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\DanceEvent;
use App\Repositories\Interfaces\IDanceEventRepository;
use App\Support\VenueSchemaHelper;

final class DanceEventRepository extends Repository implements IDanceEventRepository
{
    /** @var array<string, bool> */
    private array $columnCache = [];

    /** @return DanceEvent[] */
    public function getAllDanceEvents(): array
    {
        $rows = $this->fetchDanceRows();
        return array_map(static fn(array $r) => new DanceEvent($r), $rows);
    }

    public function findDanceEventById(int $eventId): ?DanceEvent
    {
        if ($eventId <= 0) {
            return null;
        }

        $rows = $this->fetchDanceRows('e.event_id = :event_id', [':event_id' => $eventId], 'LIMIT 1');
        if ($rows === []) {
            return null;
        }

        return new DanceEvent($rows[0]);
    }

    public function createDanceEvent(DanceEvent $event): int
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $stmtEvent = $pdo->prepare("
                INSERT INTO Event (title, event_type, availability)
                VALUES (:title, 'dance', :availability)
            ");
            $stmtEvent->execute([
                ':title' => $event->title,
                ':availability' => $event->availability,
            ]);

            $eventId = (int)$pdo->lastInsertId();
            if ($eventId <= 0) {
                throw new \RuntimeException('Unable to create parent dance event.');
            }

            $insertCols = ['event_id', 'venue_id', 'price'];
            $insertVals = [':event_id', ':venue_id', ':price'];
            $params = [
                ':event_id' => $eventId,
                ':venue_id' => $event->venue_id,
                ':price' => $event->price,
            ];

            if ($this->hasColumn('start_date')) {
                $insertCols[] = 'start_date';
                $insertVals[] = ':start_date';
                $params[':start_date'] = $event->start_date;
            }
            if ($this->hasColumn('end_date')) {
                $insertCols[] = 'end_date';
                $insertVals[] = ':end_date';
                $params[':end_date'] = $event->end_date;
            }
            if ($this->hasColumn('event_date')) {
                $insertCols[] = 'event_date';
                $insertVals[] = ':event_date';
                $params[':event_date'] = $event->event_date;
            }
            if ($this->hasColumn('session_start')) {
                $insertCols[] = 'session_start';
                $insertVals[] = ':session_start';
                $params[':session_start'] = $event->start_date;
            }
            if ($this->hasColumn('session_end')) {
                $insertCols[] = 'session_end';
                $insertVals[] = ':session_end';
                $params[':session_end'] = $event->end_date;
            }
            if ($this->hasColumn('day_display_label')) {
                $insertCols[] = 'day_display_label';
                $insertVals[] = ':day_display_label';
                $params[':day_display_label'] = $event->day_display_label;
            }
            if ($this->hasColumn('artist_id')) {
                $insertCols[] = 'artist_id';
                $insertVals[] = ':artist_id';
                $params[':artist_id'] = $event->artist_id;
            }
            if ($this->hasColumn('img_background')) {
                $insertCols[] = 'img_background';
                $insertVals[] = ':img_background';
                $params[':img_background'] = $event->img_background;
            }
            if ($this->hasColumn('page_id')) {
                $insertCols[] = 'page_id';
                $insertVals[] = ':page_id';
                $params[':page_id'] = $event->page_id;
            }
            if ($this->hasColumn('row_kind')) {
                $insertCols[] = 'row_kind';
                $insertVals[] = ':row_kind';
                $params[':row_kind'] = $event->row_kind ?: 'session';
            }
            if ($this->hasColumn('sort_order')) {
                $insertCols[] = 'sort_order';
                $insertVals[] = ':sort_order';
                $params[':sort_order'] = $event->sort_order ?? 999;
            }
            if ($this->hasColumn('session_tag')) {
                $insertCols[] = 'session_tag';
                $insertVals[] = ':session_tag';
                $params[':session_tag'] = $event->session_tag ?? '';
            }
            if ($this->hasColumn('tag_special')) {
                $insertCols[] = 'tag_special';
                $insertVals[] = ':tag_special';
                $params[':tag_special'] = $event->tag_special ? 1 : 0;
            }

            $sql = 'INSERT INTO DanceEvent (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', $insertVals) . ')';
            $stmtDance = $pdo->prepare($sql);
            $stmtDance->execute($params);

            $pdo->commit();
            return $eventId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updateDanceEvent(DanceEvent $event): void
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $check = $pdo->prepare("SELECT 1 FROM Event WHERE event_id = :id AND event_type = 'dance' LIMIT 1");
            $check->execute([':id' => $event->event_id]);
            if (!$check->fetchColumn()) {
                throw new \RuntimeException('Event not found or not a dance event.');
            }

            $stmtEvent = $pdo->prepare("UPDATE Event SET title = :title WHERE event_id = :id");
            $stmtEvent->execute([
                ':title' => $event->title,
                ':id' => $event->event_id,
            ]);

            $sets = ['venue_id = :venue_id', 'price = :price'];
            $params = [
                ':id' => $event->event_id,
                ':venue_id' => $event->venue_id,
                ':price' => $event->price,
            ];

            if ($this->hasColumn('start_date')) {
                $sets[] = 'start_date = :start_date';
                $params[':start_date'] = $event->start_date;
            }
            if ($this->hasColumn('end_date')) {
                $sets[] = 'end_date = :end_date';
                $params[':end_date'] = $event->end_date;
            }
            if ($this->hasColumn('event_date')) {
                $sets[] = 'event_date = :event_date';
                $params[':event_date'] = $event->event_date;
            }
            if ($this->hasColumn('session_start')) {
                $sets[] = 'session_start = :session_start';
                $params[':session_start'] = $event->start_date;
            }
            if ($this->hasColumn('session_end')) {
                $sets[] = 'session_end = :session_end';
                $params[':session_end'] = $event->end_date;
            }
            if ($this->hasColumn('day_display_label')) {
                $sets[] = 'day_display_label = :day_display_label';
                $params[':day_display_label'] = $event->day_display_label;
            }
            if ($this->hasColumn('artist_id')) {
                $sets[] = 'artist_id = :artist_id';
                $params[':artist_id'] = $event->artist_id;
            }
            if ($this->hasColumn('img_background')) {
                $sets[] = 'img_background = :img_background';
                $params[':img_background'] = $event->img_background;
            }
            if ($this->hasColumn('page_id')) {
                $sets[] = 'page_id = :page_id';
                $params[':page_id'] = $event->page_id;
            }
            if ($this->hasColumn('row_kind')) {
                $sets[] = 'row_kind = :row_kind';
                $params[':row_kind'] = $event->row_kind ?: 'session';
            }
            if ($this->hasColumn('sort_order')) {
                $sets[] = 'sort_order = :sort_order';
                $params[':sort_order'] = $event->sort_order ?? 999;
            }
            if ($this->hasColumn('session_tag')) {
                $sets[] = 'session_tag = :session_tag';
                $params[':session_tag'] = $event->session_tag ?? '';
            }
            if ($this->hasColumn('tag_special')) {
                $sets[] = 'tag_special = :tag_special';
                $params[':tag_special'] = $event->tag_special ? 1 : 0;
            }

            $sql = 'UPDATE DanceEvent SET ' . implode(', ', $sets) . ' WHERE event_id = :id';
            $stmtDance = $pdo->prepare($sql);
            $stmtDance->execute($params);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function deleteDanceEventById(int $eventId): bool
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $check = $pdo->prepare("SELECT 1 FROM Event WHERE event_id = :id AND event_type = 'dance' LIMIT 1");
            $check->execute([':id' => $eventId]);
            if (!$check->fetchColumn()) {
                $pdo->rollBack();
                return false;
            }

            $stmtDance = $pdo->prepare('DELETE FROM DanceEvent WHERE event_id = :id');
            $stmtDance->execute([':id' => $eventId]);

            $stmtEvent = $pdo->prepare("DELETE FROM Event WHERE event_id = :id AND event_type = 'dance'");
            $stmtEvent->execute([':id' => $eventId]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** @return list<array<string, mixed>> */
    private function fetchDanceRows(string $where = '1=1', array $params = [], string $suffix = ''): array
    {
        $pdo = $this->getConnection();
        $vpk = VenueSchemaHelper::primaryKeyColumn($pdo);
        $locExpr = VenueSchemaHelper::displayNameExpression($pdo, 'v');
        $venueTable = str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo));

        $artistJoin = $this->hasColumn('artist_id') ? 'LEFT JOIN Artist a ON a.artist_id = d.artist_id' : '';
        $artistSelect = $this->hasColumn('artist_id')
            ? 'd.artist_id, COALESCE(a.name, \'\') AS artist_name,'
            : 'NULL AS artist_id, \'\' AS artist_name,';

        $imgSelect = $this->hasColumn('img_background') ? 'd.img_background' : 'NULL AS img_background';
        $pageSelect = $this->hasColumn('page_id') ? 'd.page_id' : 'NULL AS page_id';
        $rowKindSelect = $this->hasColumn('row_kind') ? 'd.row_kind' : '\'session\' AS row_kind';
        $sortOrderSelect = $this->hasColumn('sort_order') ? 'd.sort_order' : '0 AS sort_order';
        $startSelect = $this->hasColumn('start_date') ? 'd.start_date' : 'd.session_start AS start_date';
        $endSelect = $this->hasColumn('end_date') ? 'd.end_date' : 'd.session_end AS end_date';
        $eventDateSelect = $this->hasColumn('event_date') ? 'd.event_date' : 'd.day_display_label AS event_date';

        $orderBy = $this->hasColumn('sort_order')
            ? 'ORDER BY d.sort_order ASC, d.event_id ASC'
            : 'ORDER BY ' . ($this->hasColumn('start_date') ? 'd.start_date' : 'e.event_id') . ' ASC, d.event_id ASC';

        $sql = "
            SELECT
                e.event_id,
                e.title,
                e.event_type,
                e.availability,
                {$startSelect},
                {$endSelect},
                {$eventDateSelect},
                d.venue_id,
                {$locExpr} AS venue_name,
                {$artistSelect}
                {$imgSelect},
                d.price,
                {$pageSelect},
                {$rowKindSelect},
                {$sortOrderSelect}
            FROM Event e
            INNER JOIN DanceEvent d ON d.event_id = e.event_id
            LEFT JOIN `{$venueTable}` v ON v.`{$vpk}` = d.venue_id
            {$artistJoin}
            WHERE e.event_type = 'dance'
              AND {$where}
            {$orderBy}
            {$suffix}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    private function hasColumn(string $column): bool
    {
        if (array_key_exists($column, $this->columnCache)) {
            return $this->columnCache[$column];
        }

        try {
            $stmt = $this->getConnection()->prepare('SHOW COLUMNS FROM DanceEvent LIKE :column');
            $stmt->execute([':column' => $column]);
            $exists = $stmt->fetch() !== false;
        } catch (\Throwable) {
            $exists = false;
        }

        $this->columnCache[$column] = $exists;
        return $exists;
    }
}
