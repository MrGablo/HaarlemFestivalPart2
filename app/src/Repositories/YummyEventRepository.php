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
            SELECT e.event_id, e.title, e.event_type, 
                   y.id as yummy_id, y.page_id, y.thumbnail_path, y.cuisine, y.star_rating, y.price
            FROM Event e
            INNER JOIN YummyEvent y ON e.event_id = y.event_id
            WHERE e.event_type = 'yummy'
            ORDER BY e.title ASC
        ");

        $rows = $stmt->fetchAll();
        $events = [];

        foreach ($rows as $row) {
            $events[] = $this->modelBuilder->buildEventModel($row);
        }

        return $events;
    }

    public function getYummyEventById(int $id): ?YummyEvent
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("
            SELECT e.event_id, e.title, e.event_type, 
                   y.id as yummy_id, y.page_id, y.thumbnail_path, y.cuisine, y.star_rating, y.price
            FROM Event e
            INNER JOIN YummyEvent y ON e.event_id = y.event_id
            WHERE y.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->modelBuilder->buildEventModel($row);
    }

    public function getSessionsForYummyEvent(int $yummyId): array
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("
            SELECT id, yummy_event_id, start_time, end_time, capacity 
            FROM Restaurant_session 
            WHERE yummy_event_id = :id
            ORDER BY start_time ASC
        ");
        $stmt->execute(['id' => $yummyId]);

        return $stmt->fetchAll();
    }
}
