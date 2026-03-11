<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IStoriesRepository;
use PDO;

class StoriesRepository extends Repository implements IStoriesRepository
{
    public function getAllStoriesEvents(): array
    {
        $pdo = $this->getConnection();

        $sql = "
            SELECT 
                e.event_id,
                e.title,
                s.language,
                s.age_group,
                s.story_type,
                s.location,
                s.description,
                s.start_date,
                s.end_date,
                s.price,
                s.img_background
            FROM Event e
            INNER JOIN StoriesEvent s ON e.event_id = s.event_id
            WHERE e.event_type = 'stories'
            ORDER BY s.start_date ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }
}