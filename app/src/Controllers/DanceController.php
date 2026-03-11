<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\PageRepository;
use App\Services\DanceHomeService;

class DanceController
{
    private DanceHomeService $service;

    public function __construct()
    {
        $this->service = new DanceHomeService(
            new PageRepository(),
        );
    }

    public function home(): void
    {
        \App\Utils\Session::ensureStarted();

        $vm = $this->service->getDanceHomePageViewModel();

        $content = $vm->content;

        // Assets are served at /dance/assets/ (see index.php). Always use this base so img/CSS paths work.
        $basePath = '/dance';

        require __DIR__ . '/../Views/pages/dance_home.php';
    }
}

