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

    public function updateJazzEvent(JazzEvent $event): void
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            // Update Event table (title)
            $stmt1 = $pdo->prepare("
                UPDATE Event
                SET title = :title
                WHERE event_id = :id AND event_type = 'jazz'
            ");
            $stmt1->execute([
                ':title' => $event->title,
                ':id' => $event->event_id,
            ]);

            // Optional guard: ensure row exists and is jazz
            if ($stmt1->rowCount() === 0) {
                throw new \RuntimeException('Event not found or not a jazz event.');
            }

            // Update JazzEvent table (details)
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
                ':img_background' => $event->img_background, // null ok
                ':price' => $event->price,
                ':page_id' => $event->page_id,               // null ok
                ':id' => $event->event_id,
            ]);

            if ($stmt2->rowCount() === 0) {
                // If you want to allow "no changes", remove this check.
                // Keeping it helps catch missing JazzEvent row.
                // throw new \RuntimeException('JazzEvent row not found for this event_id.');
            }

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
}
