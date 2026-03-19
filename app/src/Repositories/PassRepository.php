<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IPassRepository;

class PassRepository extends Repository implements IPassRepository
{
    public function getActivePassProductsByFestivalType(string $festivalType): array
    {
        $festivalType = strtolower(trim($festivalType));
        if ($festivalType === '') {
            return [];
        }

        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            "SELECT
                p.event_id,
                p.festival_type,
                p.pass_scope,
                p.base_price,
                p.active,
                e.title
             FROM PassEvent p
             INNER JOIN Event e ON e.event_id = p.event_id
             WHERE p.active = 1
               AND LOWER(p.festival_type) = :festival_type
               AND e.event_type = 'pass'
             ORDER BY
               CASE p.pass_scope
                   WHEN 'day' THEN 1
                   WHEN 'all_days' THEN 2
                   ELSE 99
               END,
               p.base_price ASC,
               p.event_id ASC"
        );

        $stmt->execute([':festival_type' => $festivalType]);
        $rows = $stmt->fetchAll();

        if (!is_array($rows)) {
            return [];
        }

        return array_values(array_map(function (array $row): array {
            return [
                'event_id' => (int)($row['event_id'] ?? 0),
                'festival_type' => (string)($row['festival_type'] ?? ''),
                'pass_scope' => (string)($row['pass_scope'] ?? ''),
                'base_price' => (float)($row['base_price'] ?? 0),
                'title' => (string)($row['title'] ?? ''),
                'active' => (int)($row['active'] ?? 0),
            ];
        }, $rows));
    }
}
