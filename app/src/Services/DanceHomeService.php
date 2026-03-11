<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Interfaces\IPageRepository;
use App\ViewModels\DanceHomePageViewModel;

class DanceHomeService
{
    public function __construct(
        private IPageRepository $pageRepo,
    ) {}

    public function getDanceHomePageViewModel(): DanceHomePageViewModel
    {
        $content = $this->pageRepo->getPageContentByType('Dance_Homepage');

        return new DanceHomePageViewModel($content);
    }
}

