<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\DanceHomeRepository;
use App\Repositories\PageRepository;
use App\Services\DanceArtistService;
use App\Services\DanceLocationService;
use App\Services\DanceHomeService;
use App\Utils\Session;

// Dance area: home, artist pages, and venue (location) pages.
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

        try {
            // $vm is the data object for the dance home template.
            $vm = $this->service->buildViewModel();
            require __DIR__ . '/../Views/pages/dance_home.php';
        } catch (\Throwable $e) {
            // DB/CMS failure — show a safe page instead of a white screen.
            error_log('DanceController::home: ' . $e->getMessage());
            $this->respondDanceServerError();
        }
    }

    public function artist(): void
    {
        Session::ensureStarted();

        $pageId = isset($_GET['page_id']) ? (int)$_GET['page_id'] : 0;
        if ($pageId <= 0) {
            http_response_code(404);
            require __DIR__ . '/../Views/partials/error_general.php';
            return;
        }

        try {
            $service = new DanceArtistService(new PageRepository(), new DanceHomeRepository());
            $vm = $service->getArtistPageViewModel($pageId);
            require __DIR__ . '/../Views/pages/dance_artist.php';
        } catch (\Throwable $e) {
            error_log('DanceController::artist: ' . $e->getMessage());
            $this->respondDanceServerError();
        }
    }

    public function location(): void
    {
        Session::ensureStarted();

        $pageId = isset($_GET['page_id']) ? (int) $_GET['page_id'] : 0;
        if ($pageId <= 0) {
            http_response_code(404);
            require __DIR__ . '/../Views/partials/error_general.php';
            return;
        }

        try {
            $service = new DanceLocationService(new PageRepository());
            $vm = $service->getLocationPageViewModel($pageId);
            if ($vm === null) {
                http_response_code(404);
                require __DIR__ . '/../Views/partials/error_general.php';
                return;
            }

            require __DIR__ . '/../Views/pages/dance_location.php';
        } catch (\Throwable $e) {
            error_log('DanceController::location: ' . $e->getMessage());
            $this->respondDanceServerError();
        }
    }

    private function respondDanceServerError(): void
    {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Haarlem Festival</title></head><body style="font-family:system-ui,sans-serif;padding:2rem;max-width:32rem">';
        echo '<h1>Something went wrong</h1><p>We could not load this dance page. Please try again in a moment.</p>';
        echo '<p><a href="/dance">Back to Dance home</a></p></body></html>';
    }
}
