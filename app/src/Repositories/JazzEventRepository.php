<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IJazzEventRepository;

class JazzEventRepository extends Repository implements IJazzEventRepository
{
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

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll() ?: [];
    }
}