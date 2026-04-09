<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\HistoryEvent;
use App\Repositories\Interfaces\IHistoryEventRepository;

class HistoryEventRepository extends Repository implements IHistoryEventRepository
{
    /** @return HistoryEvent[] */
    public function getAllHistoryEvents(): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->query(
            "
            SELECT
                e.event_id,
                e.title,
                e.event_type,
                e.availability,
                h.language,
                h.start_date,
                h.location,
                h.price
            FROM Event e
            INNER JOIN HistoryEvent h ON h.event_id = e.event_id
            WHERE e.event_type = 'history'
            ORDER BY h.start_date ASC, e.event_id ASC
            "
        );

        $rows = $stmt ? $stmt->fetchAll() : [];
        $rows = is_array($rows) ? $rows : [];

        return array_map(static fn(array $row): HistoryEvent => new HistoryEvent($row), $rows);
    }

    public function findHistoryEventById(int $eventId): ?HistoryEvent
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            "
            SELECT
                e.event_id,
                e.title,
                e.event_type,
                e.availability,
                h.language,
                h.start_date,
                h.location,
                h.price
            FROM Event e
            INNER JOIN HistoryEvent h ON h.event_id = e.event_id
            WHERE e.event_type = 'history'
              AND e.event_id = :id
            LIMIT 1
            "
        );
        $stmt->execute([':id' => $eventId]);
        $row = $stmt->fetch();

        return is_array($row) ? new HistoryEvent($row) : null;
    }

    public function createHistoryEvent(HistoryEvent $event): int
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $stmtEvent = $pdo->prepare(
                "INSERT INTO Event (title, event_type, availability)
                 VALUES (:title, 'history', :availability)"
            );
            $stmtEvent->execute([
                ':title' => $event->title,
                ':availability' => $event->availability,
            ]);

            $eventId = (int)$pdo->lastInsertId();
            if ($eventId <= 0) {
                throw new \RuntimeException('Unable to create parent history event.');
            }

            $stmtHistory = $pdo->prepare(
                "INSERT INTO HistoryEvent (event_id, language, start_date, location, price)
                 VALUES (:event_id, :language, :start_date, :location, :price)"
            );
            $stmtHistory->execute([
                ':event_id' => $eventId,
                ':language' => $event->language,
                ':start_date' => $event->start_date,
                ':location' => $event->location,
                ':price' => $event->price,
            ]);

            $pdo->commit();
            return $eventId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updateHistoryEvent(HistoryEvent $event): void
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $check = $pdo->prepare(
                "SELECT 1 FROM Event WHERE event_id = :id AND event_type = 'history' LIMIT 1"
            );
            $check->execute([':id' => $event->event_id]);

            if (!$check->fetchColumn()) {
                throw new \RuntimeException('Event not found or not a history event.');
            }

            $stmtEvent = $pdo->prepare(
                "UPDATE Event
                 SET title = :title,
                     availability = :availability
                 WHERE event_id = :id"
            );
            $stmtEvent->execute([
                ':title' => $event->title,
                ':availability' => $event->availability,
                ':id' => $event->event_id,
            ]);

            $stmtHistory = $pdo->prepare(
                "UPDATE HistoryEvent
                 SET language = :language,
                     start_date = :start_date,
                     location = :location,
                     price = :price
                 WHERE event_id = :id"
            );
            $stmtHistory->execute([
                ':language' => $event->language,
                ':start_date' => $event->start_date,
                ':location' => $event->location,
                ':price' => $event->price,
                ':id' => $event->event_id,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function deleteHistoryEventById(int $eventId): bool
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $check = $pdo->prepare(
                "SELECT 1 FROM Event WHERE event_id = :id AND event_type = 'history' LIMIT 1"
            );
            $check->execute([':id' => $eventId]);

            if (!$check->fetchColumn()) {
                $pdo->rollBack();
                return false;
            }

            $stmtHistory = $pdo->prepare('DELETE FROM HistoryEvent WHERE event_id = :id');
            $stmtHistory->execute([':id' => $eventId]);

            $stmtEvent = $pdo->prepare("DELETE FROM Event WHERE event_id = :id AND event_type = 'history'");
            $stmtEvent->execute([':id' => $eventId]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}