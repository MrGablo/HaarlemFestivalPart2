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
            $vm = $this->service->getYummyHomePageViewModel();

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
