<?php

namespace App\Controllers;

use App\Repositories\JazzHomeRepository;
use App\Repositories\JazzEventRepository;
use App\Services\JazzHomeService;

class JazzController
{
    private JazzHomeService $service;

    public function __construct()
    {
        $this->service = new JazzHomeService(
            new JazzHomeRepository(),
            new JazzEventRepository()
        );
    }

    public function home(): void
    {
        \App\Utils\Session::ensureStarted();

        $vm = $this->service->getJazzHomePageViewModel();

        $content = $vm->content;
        $events  = $vm->events;

        require __DIR__ . '/../Views/pages/jazz_home.php';
    }
}