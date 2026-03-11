<?php

namespace App\Controllers;

use App\Config;
use App\Repositories\PageRepository;
use App\Services\HomeService;
use App\Utils\AuthSessionData;
use App\Utils\Session;

class HomeController
{
    private HomeService $homeService;

    public function __construct()
    {
        $this->homeService = new HomeService(new PageRepository());
    }

    public function home(): void
    {
        Session::ensureStarted();

        try {
            // get viewmodel 
            $vm = $this->homeService->getHomePageViewModel();

            // extract data for the view
            $content = $vm->content;
            $categories = $vm->categories;

            // auth  
            $auth = AuthSessionData::read();
            $isLoggedIn = $auth !== null;
            $profilePicturePath = $auth['profilePicturePath'] ?? Config::DEFAULT_USER_PROFILE_IMAGE_PATH;

            // Load the view
            require __DIR__ . '/../Views/pages/home.php';

        } catch (\Throwable $e) {
            $errors = ['general' => $e->getMessage()];
            $content = [];
            $categories = [];
            $isLoggedIn = false;
            $profilePicturePath = Config::DEFAULT_USER_PROFILE_IMAGE_PATH;

            require __DIR__ . '/../Views/pages/home.php';
        }
    }
}