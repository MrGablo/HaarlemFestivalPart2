<?php

namespace App\Services;

use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\HomePageViewModel;

class HomeService
{
    public function __construct(
        private IPageRepository $pageRepo
    ) {}

    public function getHomePageViewModel(): HomePageViewModel
    {
        // get page type 
        $content = $this->pageRepo->getPageContentByType('HomePage');

        // get the tabs
        $categories = $content['schedule']['filters']['tabs'] ?? [];

        return new HomePageViewModel($content, $categories);
    }
}