<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config;
use App\Utils\AuthSessionData;

class ProgramController
{
    public function show(): void
    {
        \App\Utils\Session::ensureStarted();

        $auth = AuthSessionData::read();
        $isLoggedIn = $auth !== null;
        $profilePicturePath = $auth['profilePicturePath'] ?? Config::DEFAULT_USER_PROFILE_IMAGE_PATH;
        $currentPage = 'program';

        // load from cart/session when cart is implemented
        $totalEvents = 0;
        $subtotal = 0;
        $cartCount = $totalEvents;

        require __DIR__ . '/../Views/pages/program.php';
    }
}
