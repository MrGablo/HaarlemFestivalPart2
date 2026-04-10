<?php

namespace App\Controllers;

use App\Config;
use App\Repositories\PageRepository;
use App\Repositories\YummyEventRepository;
use App\Utils\Flash;
use App\Services\YummyHomeService;
use App\Services\YummyDetailService;
use App\Utils\AuthSessionData;
use App\Utils\Session;
use App\ViewModels\YummyHomePageViewModel;
use App\ViewModels\YummyDetailPageViewModel;

class YummyController
{
    private YummyHomeService $service;
    private YummyDetailService $detailService;

    public function __construct()
    {
        $this->service = new YummyHomeService(new PageRepository());
        $this->detailService = new YummyDetailService(new YummyEventRepository(), new PageRepository());
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
            $vm = new YummyHomePageViewModel([
                'pageTitle' => 'Yummy',
                'hero' => [
                    'titleHtml' => 'Yummy',
                    'bgImage' => '',
                ],
                'intro' => [],
                'gallery' => [
                    'images' => [],
                    'captions' => [],
                ],
                'map' => [
                    'image' => '',
                    'imageCaption' => '',
                ],
                'restaurants' => [],
            ], []);
            $isLoggedIn = false;
            $profilePicturePath = Config::DEFAULT_USER_PROFILE_IMAGE_PATH;
            $activeNav = 'yummy';

            require __DIR__ . '/../Views/pages/yummy_home.php';
        }
    }

    public function gerRestaurant(): void
    {
        Session::ensureStarted();

        $eventId = (int)($_GET['id'] ?? 0);
        if ($eventId === 0) {
            header('Location: /yummy');
            exit;
        }

        $event = $this->detailService->getEventDetails($eventId);

        if (!$event) {
            header('Location: /yummy');
            exit;
        }

        $pageContent = $event->page_id ? $this->detailService->getPageContent($event->page_id) : [];
        $sessions = $this->detailService->getEventSessions($eventId);

        $vm = new YummyDetailPageViewModel($event, $sessions, $pageContent);

        $auth = AuthSessionData::read();
        $isLoggedIn = $auth !== null;
        $activeNav = 'yummy';
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $old = Flash::getOld();

        require __DIR__ . '/../Views/pages/yummy_detail.php';
    }
}
