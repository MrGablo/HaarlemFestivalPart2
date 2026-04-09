<?php

namespace App\Services;

use App\Repositories\Interfaces\IPageRepository;
use App\Repositories\YummyEventRepository;
use App\ViewModels\YummyHomePageViewModel;

class YummyHomeService
{
    private YummyEventRepository $yummyEventRepo;

    public function __construct(
        private IPageRepository $pageRepo
    ) {
        $this->yummyEventRepo = new YummyEventRepository();
    }

    public function getHomepageContent(): array
    {
        return $this->pageRepo->getPageContentByType('Yummy_Homepage');
    }

    public function getAllYummyEvents(): array
    {
        return $this->yummyEventRepo->getAllYummyEvents();
    }
}
