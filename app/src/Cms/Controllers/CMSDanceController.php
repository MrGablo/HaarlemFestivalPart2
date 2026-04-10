<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Models\DanceEvent;
use App\Services\DanceEventService;
use App\Services\UploadService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSDanceController
{
    private DanceEventService $service;
    private UploadService $uploads;

    public function __construct()
    {
        $this->service = new DanceEventService();
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

        require __DIR__ . '/../../Views/cms/dance_events_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);
        $old = Flash::getOld();
        $pages = $this->service->allPages();
        $artists = $this->service->allArtists();
        $venues = $this->service->allVenues();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/dance_event_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();
            $event = new DanceEvent([
                'event_id' => 0,
                'event_type' => 'dance',
                'img_background' => null,
            ]);
            $this->service->hydrateEventFromInput($event, $_POST);
            $this->handleImageUpload($event, false);

            $newId = $this->service->createEvent($event);
            Flash::setSuccess('Dance event created successfully.');
            header('Location: /cms/events/dance/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
            header('Location: /cms/events/dance/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);
        $event = $this->getEventOrRedirect($id);

        $artists = $this->service->allArtists();
        $pages = $this->service->allPages();
        $venues = $this->service->allVenues();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/dance_event_edit.php';
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
            Flash::setSuccess('Dance event updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/dance/' . $id, true, 302);
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
                Flash::setErrors(['general' => 'Dance event could not be deleted.']);
                header('Location: /cms/events/dance', true, 302);
                exit;
            }

            $this->uploads->deleteImage($event->img_background, 'dance', 'event');
            Flash::setSuccess('Dance event deleted successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/dance', true, 302);
        exit;
    }

    private function getEventOrRedirect(int $id): DanceEvent
    {
        $event = $this->service->findEvent($id);
        if ($event !== null) {
            return $event;
        }

        Flash::setErrors(['general' => 'Dance event not found.']);
        header('Location: /cms/events/dance', true, 302);
        exit;
    }

    private function handleImageUpload(DanceEvent $event, bool $replaceOld): void
    {
        if (!isset($_FILES['img_background_file']) || !is_array($_FILES['img_background_file'])) {
            return;
        }
        $file = $_FILES['img_background_file'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return;
        }

        $event->img_background = $this->uploads->storeImage(
            $file,
            'dance',
            'event',
            null,
            false,
            $replaceOld ? $event->img_background : null
        );
    }
}
