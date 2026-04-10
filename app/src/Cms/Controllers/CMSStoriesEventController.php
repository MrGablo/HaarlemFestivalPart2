<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Cms\Services\CmsStoriesEventService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSStoriesEventController
{
    private CmsStoriesEventService $service;

    public function __construct()
    {
        $this->service = new CmsStoriesEventService();
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $events = $this->service->getAllStoriesEvents();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/stories_index.php';
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->service->getStoriesEventById($id);

        if ($event === null) {
            Flash::setErrors(['general' => 'Stories event not found.']);
            header('Location: /cms/events/stories', true, 302);
            exit;
        }

        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/story_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();
            $this->service->updateStoriesEvent($id, $_POST, $_FILES);
            Flash::setSuccess('Stories event updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/stories/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $deleted = $this->service->deleteStoriesEvent($id);

            if (!$deleted) {
                Flash::setErrors(['general' => 'Stories event not found or could not be deleted.']);
            } else {
                Flash::setSuccess('Stories event deleted successfully.');
            }
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/stories', true, 302);
        exit;
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        $event = (object) [
            'event_id' => 0,
            'title' => '',
            'language' => '',
            'age_group' => '',
            'story_type' => '',
            'location' => '',
            'description' => '',
            'start_date' => '',
            'end_date' => '',
            'price' => '0.00',
            'img_background' => ''
        ];

        require __DIR__ . '/../../Views/cms/story_create.php';
    }

    public function store(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();
            $newId = $this->service->createStoriesEvent($_POST, $_FILES);
            Flash::setSuccess('Stories event created successfully.');
            header('Location: /cms/events/stories/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            header('Location: /cms/events/stories/create', true, 302);
            exit;
        }
    }
}