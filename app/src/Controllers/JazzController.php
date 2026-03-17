<?php

namespace App\Controllers;

use App\Repositories\PageRepository;
use App\Repositories\JazzEventRepository;
use App\Repositories\VenueRepository;
use App\Services\JazzHomeService;
use App\Services\JazzArtistService;
use App\Utils\Flash;

class JazzController
{
    private JazzHomeService $service;

    public function __construct()
    {
        $this->service = new JazzHomeService(
            new PageRepository(),
            new JazzEventRepository(),
            new VenueRepository()
        );
    }

    public function home(): void
    {
        \App\Utils\Session::ensureStarted();

        $vm = $this->service->getJazzHomePageViewModel();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/pages/jazz_home.php';
    }

    public function artist(): void
    {
        \App\Utils\Session::ensureStarted();

        $pageId = isset($_GET['page_id']) ? (int)$_GET['page_id'] : 0;
        if ($pageId <= 0) {
            http_response_code(404);
            require __DIR__ . '/../Views/partials/error_general.php';
            return;
        }

        $tab = isset($_GET['tab']) ? (string)$_GET['tab'] : null;

        $service = new JazzArtistService(new PageRepository(), new JazzEventRepository());
        $vm = $service->getArtistPageViewModel($pageId, $tab);
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../Views/pages/jazz_artist.php';
    }
}