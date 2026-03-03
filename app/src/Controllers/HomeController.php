<?php

namespace App\Controllers;

use App\Config;
use App\Repositories\Interfaces\IHomeRepository;
use App\Services\HomeService;
use App\Utils\AuthSessionData;

class HomeController
{
    private HomeService $homeService;

    public function __construct()
    {
        $this->homeService = new HomeService();
    }

    public function home()
    {

        try {
            // Get data from the service
            $content = $this->homeService->getHomePageContent();
            $auth = AuthSessionData::read();
            $isLoggedIn = $auth !== null;
            $profilePicturePath = $auth['profilePicturePath'] ?? Config::DEFAULT_USER_PROFILE_IMAGE_PATH;

            // Load the file
            require __DIR__ . '/../Views/pages/home.php';
        } catch (\Throwable $e) {
            // Render the same view with an errors array so the partial can display it
            $errors = ['general' => $e->getMessage()];
            // Ensure variables used by the view exist
            $content = [];
            $isLoggedIn = false;
            $profilePicturePath = Config::DEFAULT_USER_PROFILE_IMAGE_PATH;

            require __DIR__ . '/../Views/pages/home.php';
        }
    }
}
