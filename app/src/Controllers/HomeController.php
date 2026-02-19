<?php

namespace App\Controllers;

use App\Config;
use App\Repositories\Interfaces\IHomeRepository;
use App\Repositories\HomeRepository;

class HomeController
{
    private IHomeRepository $homeRepository;

    public function __construct()
    {
        $this->homeRepository = new HomeRepository();
    }

    public function home()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get data from Azure
        $content = $this->homeRepository->getHomePageContent();
        $isLoggedIn = isset($_SESSION['user_id']);
        $profilePicturePath = $_SESSION['profile_picture_path'] ?? Config::DEFAULT_USER_PROFILE_IMAGE_PATH;

        // 2. Load the file from the public folder
        require __DIR__ . '/../Views/pages/home.php';
    }
}