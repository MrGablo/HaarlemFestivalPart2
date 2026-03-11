<?php

namespace App\Services;

use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\YummyHomePageViewModel;

class YummyHomeService
{
    public function __construct(
        private IPageRepository $pageRepo
    ) {}

    public function getYummyHomePageViewModel(): YummyHomePageViewModel
    {
        $content = $this->pageRepo->getPageContentByType('Yummy_Homepage');

        return new YummyHomePageViewModel($content);
    }
}
