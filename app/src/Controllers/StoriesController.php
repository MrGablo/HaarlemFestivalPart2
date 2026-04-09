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
}