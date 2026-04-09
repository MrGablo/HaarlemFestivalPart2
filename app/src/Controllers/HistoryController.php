<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\HistoryEventRepository;
use App\Repositories\PageRepository;
use App\Services\HistoryDetailService;
use App\Services\HistoryHomeService;

class HistoryController
{
    private HistoryHomeService $homeService;

    public function __construct()
    {
        $pageRepository = new PageRepository();

        $this->homeService = new HistoryHomeService(
            $pageRepository,
            new HistoryEventRepository()
        );
    }

    public function home(): void
    {
        \App\Utils\Session::ensureStarted();

        $vm = $this->homeService->getHistoryHomePageViewModel();

        require __DIR__ . '/../Views/pages/history_home.php';
    }

    public function detail(): void
    {
        \App\Utils\Session::ensureStarted();

        $pageId = isset($_GET['page_id']) ? (int)$_GET['page_id'] : 0;
        if ($pageId <= 0) {
            http_response_code(404);
            require __DIR__ . '/../Views/partials/error_general.php';
            return;
        }

        try {
            $service = new HistoryDetailService(new PageRepository());
            $vm = $service->getHistoryDetailPageViewModel($pageId);

            require __DIR__ . '/../Views/pages/history_detail.php';
        } catch (\Throwable $e) {
            http_response_code(404);
            require __DIR__ . '/../Views/partials/error_general.php';
        }
    }
}