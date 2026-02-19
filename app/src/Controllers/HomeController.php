<?php

namespace App\Controllers;

use App\Config;
use App\Repositories\Interfaces\IHomeRepository;
use App\Repositories\HomeRepository;
use App\Utils\AuthSessionData;

class HomeController
{
    private IHomeRepository $homeRepository;

    public function __construct()
    {
        $this->homeRepository = new HomeRepository();
    }

    public function home()
    {
        \App\Utils\Session::ensureStarted();

        // Get data from Azure
        $content = $this->homeRepository->getHomePageContent();
        $auth = AuthSessionData::read();
        $isLoggedIn = $auth !== null;
        $profilePicturePath = $auth['profilePicturePath'] ?? Config::DEFAULT_USER_PROFILE_IMAGE_PATH;

        // 2. Load the file from the public folder
        require __DIR__ . '/../Views/pages/home.php';
    }
}
