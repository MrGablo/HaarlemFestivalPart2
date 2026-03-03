<?php

namespace App\Services;

use App\Repositories\HomeRepository;
use App\Repositories\Interfaces\IHomeRepository;

class HomeService
{
    private IHomeRepository $homeRepository;

    public function __construct(IHomeRepository $homeRepository = null)
    {
        $this->homeRepository = $homeRepository ?? new HomeRepository();
    }

    public function getHomePageContent(): array
    {
        return $this->homeRepository->getHomePageContent();
    }
}
