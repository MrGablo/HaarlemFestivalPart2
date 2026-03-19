<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\YummyEvent;
use App\Services\EventModelBuilderService;

class YummyEventRepository extends Repository
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
}
