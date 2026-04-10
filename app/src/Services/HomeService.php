<?php

namespace App\Services;

use App\Cms\PageBuilder\Builders\HomePageBuilder;
use App\Cms\PageBuilder\Content\HomePageContentViewModel;
use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\HomePageViewModel;

class HomeService
{
    public function __construct(
        private IPageRepository $pageRepo,
        private HomePageBuilder $builder = new HomePageBuilder()
    ) {}

    public function getHomePageViewModel(): HomePageViewModel
    {
        /** @var HomePageContentViewModel $page */
        $page = $this->builder->buildViewModel(
            $this->pageRepo->getPageContentByType($this->builder->pageType())
        );

        return new HomePageViewModel($page->content, $page->categories);
    }
}