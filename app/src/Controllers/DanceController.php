<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\DanceHomeRepository;
use App\Repositories\PageRepository;
use App\Services\DanceHomeService;
use App\Utils\Session;
use App\ViewModels\DanceHomePageViewModel;

/**
 * Dance festival section: serves the homepage with a fully populated {@see DanceHomePageViewModel}.
 */
final class DanceController
{
    private DanceHomeService $service;

    public function __construct()
    {
        $this->service = new DanceHomeService(
            new PageRepository(),
            new DanceHomeRepository(),
        );
    }

    public function home(): void
    {
        Session::ensureStarted();

        /** @var DanceHomePageViewModel $vm */
        $vm = $this->service->buildViewModel();

        require __DIR__ . '/../Views/pages/dance_home.php';
    }
}
