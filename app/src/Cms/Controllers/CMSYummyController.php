<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Models\YummyEvent;
use App\Services\YummyEventService;
use App\Services\UploadService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSYummyController
{
    private YummyEventService $service;
    private UploadService $uploads;

    public function __construct()
    {
        $this->service = new YummyEventService();
        $this->uploads = new UploadService();

        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $events = $this->service->allEvents();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/yummy_events_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);

        $old = Flash::getOld();
        $pages = $this->service->allPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/yummy_event_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $event = new YummyEvent([
                'event_id' => 0,
                'event_type' => 'yummy',
                'thumbnail_path' => null,
            ]);
            $this->service->hydrateEventFromInput($event, $_POST);
            $this->handleImageUpload($event, false);

            $newId = $this->service->createEvent($event);
            Flash::setSuccess('Yummy event created successfully.');
            header('Location: /cms/events/yummy/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
            header('Location: /cms/events/yummy/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->getEventOrRedirect($id);

        $pages = $this->service->allPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/yummy_event_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->getEventOrRedirect($id);

        try {
            Csrf::assertPost();

            $this->service->hydrateEventFromInput($event, $_POST);
            $this->handleImageUpload($event, true);

            $this->service->updateEvent($event);
            Flash::setSuccess('Yummy event updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/yummy/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $event = $this->getEventOrRedirect($id);

            $deleted = $this->service->deleteEvent($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'Yummy event could not be deleted.']);
                header('Location: /cms/events/yummy', true, 302);
                exit;
            }

            if ($event->thumbnail_path) {
                $this->uploads->deleteImage($event->thumbnail_path, 'yummy', 'event');
            }
            Flash::setSuccess('Yummy event deleted successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/yummy', true, 302);
        exit;
    }

    private function getEventOrRedirect(int $id): YummyEvent
    {
        $event = $this->service->findEvent($id);
        if ($event !== null) {
            return $event;
        }

        Flash::setErrors(['general' => 'Yummy event not found.']);
        header('Location: /cms/events/yummy', true, 302);
        exit;
    }

    private function handleImageUpload(YummyEvent $event, bool $replaceOld): void
    {
        if (!isset($_FILES['thumbnail_path_file']) || !is_array($_FILES['thumbnail_path_file'])) {
            return;
        }

        $file = $_FILES['thumbnail_path_file'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return;
        }

        $event->thumbnail_path = $this->uploads->storeImage(
            $file,
            'yummy',
            'event',
            null,
            false,
            $replaceOld ? $event->thumbnail_path : null
        );
    }
}