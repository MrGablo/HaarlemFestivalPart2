<?php
namespace App\Repositories;

use App\Repositories\Interfaces\IHomeRepository; 
use PDO;

class HomeRepository extends Repository implements IHomeRepository
{
    public function getHomePageContent(): array
    {
        $sql = "SELECT content_json FROM pages WHERE slug = 'home'";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return json_decode($result['content_json'], true);
        }

        return [];
    }
}