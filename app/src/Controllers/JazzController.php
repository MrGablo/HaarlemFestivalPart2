<?php

namespace App\Controllers;

use App\Repositories\ArtistRepository;
use App\Repositories\JazzEventRepository;
use App\Repositories\PageRepository;
use App\Services\JazzArtistService;
use App\Services\JazzHomeService;

class JazzController
{
    private JazzHomeService $service;

    public function __construct()
    {
        $this->service = new JazzHomeService(
            new PageRepository(),
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

    public function artist(): void
    {
        \App\Utils\Session::ensureStarted();

        $artistRepo = new ArtistRepository();
        $artistId = isset($_GET['artist_id']) ? (int)$_GET['artist_id'] : 0;

        if ($artistId <= 0) {
            $pageId = isset($_GET['page_id']) ? (int)$_GET['page_id'] : 0;
            if ($pageId > 0) {
                $artist = $artistRepo->findArtistByPageId($pageId);
                $artistId = $artist?->artist_id ?? 0;
            }
        }

        if ($artistId <= 0) {
            http_response_code(404);
            $errors = ['general' => 'Artist not found.'];
            require __DIR__ . '/../Views/partials/error_general.php';
            return;
        }

        $tab = isset($_GET['tab']) ? (string)$_GET['tab'] : null;

        $service = new JazzArtistService(new PageRepository(), new JazzEventRepository(), $artistRepo);

        try {
            $vm = $service->getArtistPageViewModel($artistId, $tab);
        } catch (\Throwable $e) {
            http_response_code(404);
            $errors = ['general' => $e->getMessage()];
            require __DIR__ . '/../Views/partials/error_general.php';
            return;
        }

        $content = $vm->content;
        $events  = $vm->events;
        $artistId = $vm->artistId;
        $activeTab = $vm->activeTab;

        require __DIR__ . '/../Views/pages/jazz_artist.php';
    }
}