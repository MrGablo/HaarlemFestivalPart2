<?php

namespace App\Controllers;

use App\Config;
use App\Repositories\PageRepository;
use App\Services\YummyHomeService;
use App\Utils\AuthSessionData;
use App\Utils\Session;
use App\ViewModels\YummyHomePageViewModel;

class YummyController
{
    private YummyHomeService $service;

    public function __construct()
    {
        $this->service = new YummyHomeService(new PageRepository());
    }

    public function home(): void
    {
        Session::ensureStarted();

        try {
            // Fetch raw underlying data from the Service
            $content = $this->service->getHomepageContent();
            $events = $this->service->getAllYummyEvents();

            // Assemble the ViewModel directly in the Controller
            $vm = new YummyHomePageViewModel($content, $events);

            $auth = AuthSessionData::read();
            $isLoggedIn = $auth !== null;
            $profilePicturePath = $auth['profilePicturePath'] ?? Config::DEFAULT_USER_PROFILE_IMAGE_PATH;
            $activeNav = 'yummy';

            require __DIR__ . '/../Views/pages/yummy_home.php';
        } catch (\Throwable $e) {
            $vm = new YummyHomePageViewModel();
            $isLoggedIn = false;
            $profilePicturePath = Config::DEFAULT_USER_PROFILE_IMAGE_PATH;
            $activeNav = 'yummy';

            require __DIR__ . '/../Views/pages/yummy_home.php';
        }
    }
}
