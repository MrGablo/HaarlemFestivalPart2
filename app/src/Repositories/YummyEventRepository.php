<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\YummyEvent;
use App\Repositories\Interfaces\IYummyEventRepository;
use App\Services\EventModelBuilderService;

class YummyEventRepository extends Repository implements IYummyEventRepository
{
    private EventModelBuilderService $modelBuilder;

    public function __construct()
    {
        $this->modelBuilder = new EventModelBuilderService();
    }

    /**
     * @return YummyEvent[]
     */
    public function getAllYummyEvents(): array
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->query("
            SELECT MIN(e.event_id) as event_id, e.title, MIN(e.event_type) as event_type, MAX(e.availability) as availability,
                   MIN(y.id) as yummy_id, MIN(y.page_id) as page_id, MIN(y.thumbnail_path) as thumbnail_path, 
                   MAX(y.cuisine) as cuisine, MAX(y.star_rating) as star_rating, MAX(y.price) as price
            FROM Event e
            INNER JOIN YummyEvent y ON e.event_id = y.event_id
            WHERE e.event_type = 'yummy'
            GROUP BY e.title
            ORDER BY e.title ASC
        ");

        $rows = $stmt->fetchAll();
        $events = [];

        foreach ($rows as $row) {
            $events[] = $this->modelBuilder->buildEventModel($row);
        }

        return $events;
    }

    public function getEventDetails(int $eventId): ?YummyEvent
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("
            SELECT e.event_id, e.title, e.event_type, e.availability,
                   y.id as yummy_id, y.page_id, y.thumbnail_path, y.cuisine, y.star_rating, y.price
            FROM Event e
            INNER JOIN YummyEvent y ON e.event_id = y.event_id
            WHERE e.event_id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $eventId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->modelBuilder->buildEventModel($row);
    }

    public function getSessionsForYummyEvent(int $eventId): array
    {
        $pdo = $this->getConnection();

        $stmtTitle = $pdo->prepare("SELECT title FROM Event WHERE event_id = :id");
        $stmtTitle->execute(['id' => $eventId]);
        $title = $stmtTitle->fetchColumn();

        if (!$title) return [];

        $stmt = $pdo->prepare("
            SELECT y.id, y.start_time, y.end_time, e.event_id,
                   (e.availability - COALESCE(SUM(r.adult_count + r.children_count), 0)) AS capacity
            FROM YummyEvent y
            INNER JOIN Event e ON y.event_id = e.event_id
            LEFT JOIN Reservation r ON y.id = r.yummy_event_id
            WHERE e.title = :title
            GROUP BY y.id, y.start_time, y.end_time, e.availability, e.event_id
            ORDER BY y.start_time ASC
        ");
        $stmt->execute(['title' => $title]);

        return $stmt->fetchAll();
    }

    public function getAllYummyEventsForCMS(): array
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->query("
            SELECT e.event_id, e.title, e.event_type, e.availability,
                   y.id as yummy_id, y.start_time, y.end_time, y.page_id, y.thumbnail_path, y.cuisine, y.star_rating, y.price
            FROM Event e
            INNER JOIN YummyEvent y ON e.event_id = y.event_id
            WHERE e.event_type = 'yummy'
            ORDER BY y.start_time ASC, e.event_id ASC
        ");

        $rows = $stmt->fetchAll();
        $events = [];

        foreach ($rows as $row) {
            $events[] = $this->modelBuilder->buildEventModel($row);
        }

        return $events;
    }

    public function findYummyEventById(int $eventId): ?YummyEvent
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("
            SELECT e.event_id, e.title, e.event_type, e.availability,
                   y.id as yummy_id, y.start_time, y.end_time, y.page_id, y.thumbnail_path, y.cuisine, y.star_rating, y.price
            FROM Event e
            INNER JOIN YummyEvent y ON e.event_id = y.event_id
            WHERE e.event_id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $eventId]);
        $row = $stmt->fetch();

        if (!$row) return null;
        return clone $this->modelBuilder->buildEventModel($row);
    }

    public function createYummyEvent(YummyEvent $event): int
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $stmtEvent = $pdo->prepare(
                "INSERT INTO Event (title, event_type, availability)
                 VALUES (:title, 'yummy', :availability)"
            );
            $stmtEvent->execute([
                ':title' => $event->title,
                ':availability' => $event->availability,
            ]);

            $eventId = (int)$pdo->lastInsertId();
            if ($eventId <= 0) {
                throw new \RuntimeException('Unable to create parent event.');
            }

            $stmtYummy = $pdo->prepare(
                "INSERT INTO YummyEvent (event_id, start_time, end_time, thumbnail_path, page_id, cuisine, star_rating, price)
                 VALUES (:event_id, :start_time, :end_time, :thumbnail_path, :page_id, :cuisine, :star_rating, :price)"
            );
            $stmtYummy->execute([
                ':event_id' => $eventId,
                ':start_time' => $event->start_time ?: null,
                ':end_time' => $event->end_time ?: null,
                ':thumbnail_path' => $event->thumbnail_path ?: null,
                ':page_id' => $event->page_id ?: null,
                ':cuisine' => $event->cuisine,
                ':star_rating' => $event->star_rating,
                ':price' => $event->price,
            ]);

            $pdo->commit();
            return $eventId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updateYummyEvent(YummyEvent $event): void
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $check = $pdo->prepare("SELECT 1 FROM Event WHERE event_id = :id AND event_type = 'yummy' LIMIT 1");
            $check->execute([':id' => $event->event_id]);
            if (!$check->fetchColumn()) throw new \RuntimeException('Yummy event not found.');

            $stmtEvent = $pdo->prepare("UPDATE Event SET title = :title, availability = :availability WHERE event_id = :id");
            $stmtEvent->execute([
                ':title' => $event->title,
                ':availability' => $event->availability,
                ':id' => $event->event_id,
            ]);

            $updateFields = [
                ':start_time' => $event->start_time ?: null,
                ':end_time' => $event->end_time ?: null,
                ':page_id' => $event->page_id ?: null,
                ':cuisine' => $event->cuisine,
                ':star_rating' => $event->star_rating,
                ':price' => $event->price,
                ':id' => $event->event_id,
            ];

            $imgSql = "";
            if ($event->thumbnail_path !== null) {
                $imgSql = ", thumbnail_path = :thumbnail_path";
                $updateFields[':thumbnail_path'] = $event->thumbnail_path;
            }

            $stmtYummy = $pdo->prepare(
                "UPDATE YummyEvent
                 SET start_time = :start_time, end_time = :end_time, page_id = :page_id, cuisine = :cuisine, star_rating = :star_rating, price = :price {$imgSql}
                 WHERE event_id = :id"
            );
            $stmtYummy->execute($updateFields);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function deleteYummyEventById(int $eventId): bool
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $check = $pdo->prepare("SELECT 1 FROM Event WHERE event_id = :id AND event_type = 'yummy' LIMIT 1");
            $check->execute([':id' => $eventId]);
            if (!$check->fetchColumn()) {
                $pdo->rollBack();
                return false;
            }

            $stmtYummy = $pdo->prepare('DELETE FROM YummyEvent WHERE event_id = :id');
            $stmtYummy->execute([':id' => $eventId]);

            $stmtEvent = $pdo->prepare("DELETE FROM Event WHERE event_id = :id AND event_type = 'yummy'");
            $stmtEvent->execute([':id' => $eventId]);

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
