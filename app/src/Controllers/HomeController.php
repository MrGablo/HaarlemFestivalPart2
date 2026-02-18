<?php

namespace App\Controllers;

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
        $profilePicturePath = $_SESSION['profile_picture_path'] ?? '/assets/img/default-user.png';

        // Load the view file
        require __DIR__ . '/../Views/pages/home.php';
    }
}