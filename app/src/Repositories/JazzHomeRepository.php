<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IJazzHomeRepository;

class JazzHomeRepository extends Repository implements IJazzHomeRepository
{
    public function getJazzHomePageContent(): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("SELECT Content FROM Page WHERE Page_Type = :type LIMIT 1");
        $stmt->execute([':type' => 'Jazz_Homepage']);
        $row = $stmt->fetch();

        if (!$row || empty($row['Content'])) {
            return [];
        }

        $decoded = json_decode((string)$row['Content'], true);
        return is_array($decoded) ? $decoded : [];
    }
}