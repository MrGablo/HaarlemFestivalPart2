<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\JazzEvent;
use App\Repositories\Interfaces\IJazzEventRepository;
use PDO;

class JazzEventRepository extends Repository implements IJazzEventRepository
{
    private function baseSelectSql(): string
    {
        return "
            SELECT
                e.event_id,
                e.title,
                e.event_type,
                j.start_date,
                j.end_date,
                j.location,
                j.artist_id,
                COALESCE(a.name, '') AS artist_name,
                j.img_background,
                j.price,
                a.page_id AS page_id
            FROM Event e
            JOIN JazzEvent j ON j.event_id = e.event_id
            LEFT JOIN Artist a ON a.artist_id = j.artist_id
        ";
    }

    /** @return JazzEvent[] */
    public function getAllJazzEvents(): array
    {
        $pdo = $this->getConnection();

        $sql = $this->baseSelectSql() . "
            WHERE e.event_type = 'jazz'
            ORDER BY j.start_date ASC
        ";

        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(fn(array $row) => new JazzEvent($row), $rows);
    }

    public function findJazzEventById(int $eventId): ?JazzEvent
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare($this->baseSelectSql() . "
            WHERE e.event_id = :id AND e.event_type = 'jazz'
            LIMIT 1
        ");
        $stmt->execute([':id' => $eventId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

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

            $stmtJazz = $pdo->prepare("\n                INSERT INTO JazzEvent (\n                    event_id,\n                    start_date,\n                    end_date,\n                    location,\n                    artist_id,\n                    img_background,\n                    price\n                ) VALUES (\n                    :event_id,\n                    :start_date,\n                    :end_date,\n                    :location,\n                    :artist_id,\n                    :img_background,\n                    :price\n                )\n            ");

            $stmtJazz->execute([
                ':event_id' => $eventId,
                ':start_date' => $event->start_date,
                ':end_date' => $event->end_date,
                ':location' => $event->location,
                ':artist_id' => $event->artist_id,
                ':img_background' => $event->img_background,
                ':price' => $event->price,
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
            $check = $pdo->prepare("\n                SELECT 1\n                FROM Event\n                WHERE event_id = :id AND event_type = 'jazz'\n                LIMIT 1\n            ");
            $check->execute([':id' => $event->event_id]);

            if (!$check->fetchColumn()) {
                throw new \RuntimeException('Event not found or not a jazz event.');
            }

            $stmt1 = $pdo->prepare("\n                UPDATE Event\n                SET title = :title\n                WHERE event_id = :id\n            ");
            $stmt1->execute([
                ':title' => $event->title,
                ':id' => $event->event_id,
            ]);

            $stmt2 = $pdo->prepare("\n                UPDATE JazzEvent\n                SET start_date = :start_date,\n                    end_date = :end_date,\n                    location = :location,\n                    artist_id = :artist_id,\n                    img_background = :img_background,\n                    price = :price\n                WHERE event_id = :id\n            ");
            $stmt2->execute([
                ':start_date' => $event->start_date,
                ':end_date' => $event->end_date,
                ':location' => $event->location,
                ':artist_id' => $event->artist_id,
                ':img_background' => $event->img_background,
                ':price' => $event->price,
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
        $eventIds = array_values(array_filter(array_map('intval', $eventIds), fn($value) => $value > 0));
        if (count($eventIds) === 0) {
            return [];
        }

        $pdo = $this->getConnection();
        $placeholders = implode(',', array_fill(0, count($eventIds), '?'));

        $sql = $this->baseSelectSql() . "
            WHERE e.event_type = 'jazz'
              AND e.event_id IN ($placeholders)
            ORDER BY j.start_date ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($eventIds);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(fn(array $row) => new JazzEvent($row), $rows);
    }

    public function getJazzEventsByArtistId(int $artistId): array
    {
        if ($artistId <= 0) {
            return [];
        }

        $pdo = $this->getConnection();

        $stmt = $pdo->prepare($this->baseSelectSql() . "
            WHERE e.event_type = 'jazz'
              AND j.artist_id = :artist_id
            ORDER BY j.start_date ASC
        ");
        $stmt->execute([':artist_id' => $artistId]);

                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(fn(array $row) => new JazzEvent($row), $rows);
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

            $stmtJazz = $pdo->prepare('DELETE FROM JazzEvent WHERE event_id = :id');
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