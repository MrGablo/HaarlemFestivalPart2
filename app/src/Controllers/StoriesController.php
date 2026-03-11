<?php

namespace App\Controllers;

use App\Repositories\PageRepository;
use App\Repositories\StoriesRepository;
use App\Services\StoriesService;

class StoriesController
{
    private StoriesService $service;

    public function __construct()
    {
        // This is where you instantiate the service manually 
        // just like the JazzController does.
        $this->service = new StoriesService(
            new StoriesRepository(),
            new PageRepository()
        );
    }

    public function index(): void
    {
        \App\Utils\Session::ensureStarted();

        // Get the ViewModel from the service
        $viewModel = $this->service->getStoriesPageData();

        // You can extract these like Jazz does if your view uses $content/$events
        $content = $viewModel->pageContent;
        $events  = $viewModel->events;

        require __DIR__ . '/../Views/pages/stories_home.php';
    }
}