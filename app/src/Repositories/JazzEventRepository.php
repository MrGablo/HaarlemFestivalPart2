<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\JazzEvent;
use App\Repositories\Interfaces\IJazzEventRepository;
//TODO make this a general EventRepository with a type field, and make JazzEventRepository extend it with jazz-specific methods? For now, this is fine as we only have jazz events.
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
                j.artist_name,
                j.img_background,
                j.price,
                j.page_id
            FROM Event e
            JOIN JazzEvent j ON j.event_id = e.event_id
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
                j.artist_name,
                j.img_background,
                j.price,
                j.page_id
            FROM Event e
            JOIN JazzEvent j ON j.event_id = e.event_id
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

            $stmtJazz = $pdo->prepare("\n                INSERT INTO JazzEvent (\n                    event_id,\n                    start_date,\n                    end_date,\n                    location,\n                    artist_name,\n                    img_background,\n                    price,\n                    page_id\n                ) VALUES (\n                    :event_id,\n                    :start_date,\n                    :end_date,\n                    :location,\n                    :artist_name,\n                    :img_background,\n                    :price,\n                    :page_id\n                )\n            ");

            $stmtJazz->execute([
                ':event_id' => $eventId,
                ':start_date' => $event->start_date,
                ':end_date' => $event->end_date,
                ':location' => $event->location,
                ':artist_name' => $event->artist_name,
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
                artist_name = :artist_name,
                img_background = :img_background,
                price = :price,
                page_id = :page_id
            WHERE event_id = :id
        ");
            $stmt2->execute([
                ':start_date' => $event->start_date,
                ':end_date' => $event->end_date,
                ':location' => $event->location,
                ':artist_name' => $event->artist_name,
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
            j.artist_name,
            j.img_background,
            j.price,
            j.page_id
        FROM Event e
        JOIN JazzEvent j ON j.event_id = e.event_id
        WHERE e.event_type = 'jazz'
          AND e.event_id IN ($placeholders)
        ORDER BY j.start_date ASC
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($eventIds);

        $rows = $stmt->fetchAll() ?: [];
        return array_map(fn(array $r) => new \App\Models\JazzEvent($r), $rows);
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
