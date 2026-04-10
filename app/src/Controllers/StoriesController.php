<?php

namespace App\Controllers;

use App\Repositories\PageRepository;
use App\Repositories\StoriesRepository;
use App\Services\StoriesService;
use App\Services\StoriesDetailService;

class StoriesController
{
    private StoriesService $service;

    public function __construct()
    {
        $this->service = new StoriesService(
            new StoriesRepository(),
            new PageRepository()
        );
    }

    public function index(): void
    {
        \App\Utils\Session::ensureStarted();

        try {
            $viewModel = $this->service->getStoriesPageData();

            require __DIR__ . '/../Views/pages/stories_home.php';

        } catch (\Exception $e) {
            echo "Something went wrong loading the stories page.";
            
        }
    }
    
    public function detail(): void
{
    \App\Utils\Session::ensureStarted();

    $pageId = isset($_GET['page_id']) ? (int)$_GET['page_id'] : 0;
    if ($pageId <= 0) {
        http_response_code(404);
        echo 'Stories detail page not found.';
        return;
    }

    try {
        $service = new StoriesDetailService(new PageRepository(), new StoriesRepository());
        $vm = $service->getStoriesDetailPageViewModel($pageId);

        require __DIR__ . '/../Views/pages/stories_detail.php';
    } catch (\Throwable $e) {
        http_response_code(404);
        echo 'Stories detail page not found.';
    }
}
}