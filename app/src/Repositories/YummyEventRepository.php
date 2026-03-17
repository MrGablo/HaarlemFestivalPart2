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
                   y.id as yummy_id, y.thumbnail_path, y.cuisine, y.star_rating, y.price
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
}
