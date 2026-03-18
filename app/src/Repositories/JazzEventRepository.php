<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\JazzEvent;
use App\Repositories\Interfaces\IJazzEventRepository;

class JazzEventRepository extends Repository implements IJazzEventRepository
{
    /** @return JazzEvent[] */
    public function getAllJazzEvents(): array
    {
        $pdo = $this->getConnection();

        $sql = "
            SELECT
                e.event_id,
                e.title,
                e.event_type,
                j.start_date,
                j.end_date,
                j.location,
                j.artist_id,
                a.name AS artist_name,
                j.img_background,
                j.price,
                j.page_id
            FROM Event e
            JOIN JazzEvent j ON j.event_id = e.event_id
            LEFT JOIN Artist a ON a.artist_id = j.artist_id
            WHERE e.event_type = 'jazz'
            ORDER BY j.start_date ASC
        ";

        $rows = $pdo->query($sql)->fetchAll() ?: [];
        return array_map(fn(array $r) => new JazzEvent($r), $rows);
    }

    public function findJazzEventById(int $eventId): ?JazzEvent
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("
            SELECT
                e.event_id,
                e.title,
                e.event_type,
                j.start_date,
                j.end_date,
                j.location,
                j.artist_id,
                a.name AS artist_name,
                j.img_background,
                j.price,
                j.page_id
            FROM Event e
            JOIN JazzEvent j ON j.event_id = e.event_id
            LEFT JOIN Artist a ON a.artist_id = j.artist_id
            WHERE e.event_id = :id AND e.event_type = 'jazz'
            LIMIT 1
        ");
        $stmt->execute([':id' => $eventId]);
        $row = $stmt->fetch();

        return $row ? new JazzEvent($row) : null;
    }

    public function createJazzEvent(JazzEvent $event): int
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $stmtEvent = $pdo->prepare("\n                INSERT INTO Event (title, event_type)\n                VALUES (:title, 'jazz')\n            ");
            $stmtEvent->execute([
                ':title' => $event->title,
            ]);

            $eventId = (int)$pdo->lastInsertId();
            if ($eventId <= 0) {
                throw new \RuntimeException('Unable to create parent event.');
            }

            $stmtJazz = $pdo->prepare("\n                INSERT INTO JazzEvent (\n                    event_id,\n                    start_date,\n                    end_date,\n                    location,\n                    artist_id,\n                    img_background,\n                    price,\n                    page_id\n                ) VALUES (\n                    :event_id,\n                    :start_date,\n                    :end_date,\n                    :location,\n                    :artist_id,\n                    :img_background,\n                    :price,\n                    :page_id\n                )\n            ");

            $stmtJazz->execute([
                ':event_id' => $eventId,
                ':start_date' => $event->start_date,
                ':end_date' => $event->end_date,
                ':location' => $event->location,
                ':artist_id' => $event->artist_id,
                ':img_background' => $event->img_background,
                ':price' => $event->price,
                ':page_id' => $event->page_id,
            ]);

            $pdo->commit();
            return $eventId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updateJazzEvent(JazzEvent $event): void
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            //  1) Ensure the parent event exists and is jazz (MATCH check, not AFFECTED rows)
            $check = $pdo->prepare("
            SELECT 1
            FROM Event
            WHERE event_id = :id AND event_type = 'jazz'
            LIMIT 1
        ");
            $check->execute([':id' => $event->event_id]);

            if (!$check->fetchColumn()) {
                throw new \RuntimeException('Event not found or not a jazz event.');
            }

            //  2) Update Event (no rowCount guard)
            $stmt1 = $pdo->prepare("
            UPDATE Event
            SET title = :title
            WHERE event_id = :id
        ");
            $stmt1->execute([
                ':title' => $event->title,
                ':id' => $event->event_id,
            ]);

            //  3) Update JazzEvent (no rowCount guard; 0 rows can mean “no changes”)
            $stmt2 = $pdo->prepare("
            UPDATE JazzEvent
            SET start_date = :start_date,
                end_date = :end_date,
                location = :location,
                artist_id = :artist_id,
                img_background = :img_background,
                price = :price,
                page_id = :page_id
            WHERE event_id = :id
        ");
            $stmt2->execute([
                ':start_date' => $event->start_date,
                ':end_date' => $event->end_date,
                ':location' => $event->location,
                ':artist_id' => $event->artist_id,
                ':img_background' => $event->img_background,
                ':price' => $event->price,
                ':page_id' => $event->page_id,
                ':id' => $event->event_id,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function getJazzEventsByIds(array $eventIds): array
    {
        $eventIds = array_values(array_filter(array_map('intval', $eventIds), fn($v) => $v > 0));
        if (count($eventIds) === 0) return [];

        $pdo = $this->getConnection();

        $placeholders = implode(',', array_fill(0, count($eventIds), '?'));

        $sql = "
        SELECT
            e.event_id,
            e.title,
            e.event_type,
            j.start_date,
            j.end_date,
            j.location,
            j.artist_id,
            a.name AS artist_name,
            j.img_background,
            j.price,
            j.page_id
        FROM Event e
        JOIN JazzEvent j ON j.event_id = e.event_id
        LEFT JOIN Artist a ON a.artist_id = j.artist_id
        WHERE e.event_type = 'jazz'
          AND e.event_id IN ($placeholders)
        ORDER BY j.start_date ASC
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($eventIds);

        $rows = $stmt->fetchAll() ?: [];
        return array_map(fn(array $r) => new \App\Models\JazzEvent($r), $rows);
    }

    public function getJazzEventsByPageId(int $pageId): array
    {
        if ($pageId <= 0) {
            return [];
        }

        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("\n            SELECT\n                e.event_id,\n                e.title,\n                e.event_type,\n                j.start_date,\n                j.end_date,\n                j.location,\n                j.artist_id,\n                a.name AS artist_name,\n                j.img_background,\n                j.price,\n                j.page_id\n            FROM Event e\n            JOIN JazzEvent j ON j.event_id = e.event_id\n            LEFT JOIN Artist a ON a.artist_id = j.artist_id\n            WHERE e.event_type = 'jazz'\n              AND j.page_id = :page_id\n            ORDER BY j.start_date ASC\n        ");

        $stmt->execute([':page_id' => $pageId]);

        $rows = $stmt->fetchAll() ?: [];
        return array_map(fn(array $r) => new JazzEvent($r), $rows);
    }

    public function deleteJazzEventById(int $eventId): bool
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $check = $pdo->prepare("\n                SELECT 1\n                FROM Event\n                WHERE event_id = :id AND event_type = 'jazz'\n                LIMIT 1\n            ");
            $check->execute([':id' => $eventId]);

            if (!$check->fetchColumn()) {
                $pdo->rollBack();
                return false;
            }

            $stmtJazz = $pdo->prepare("DELETE FROM JazzEvent WHERE event_id = :id");
            $stmtJazz->execute([':id' => $eventId]);

            $stmtEvent = $pdo->prepare("DELETE FROM Event WHERE event_id = :id AND event_type = 'jazz'");
            $stmtEvent->execute([':id' => $eventId]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
