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
        // Get data from Azure
        $content = $this->homeRepository->getHomePageContent();

        // 2. Load the file from the public folder
        require __DIR__ . '/../../public/home.php';
    }
}