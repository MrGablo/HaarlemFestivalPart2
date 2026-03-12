<?php

namespace App\Repositories;

use App\Repositories\Interfaces\IHomeRepository;

class HomeRepository extends PageRepository implements IHomeRepository
{
    public function getHomePageContent(): array
    {
        return $this->getPageContentByType('HomePage');
    }
}