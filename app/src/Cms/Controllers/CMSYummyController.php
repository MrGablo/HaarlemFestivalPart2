<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Models\YummyEvent;
use App\Services\YummyEventService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSYummyController
{
    private YummyEventService $service;

    public function __construct()
    {
        $this->service = new YummyEventService();
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
        $detailPages = $this->service->allYummyDetailPages();
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
            ]);
            $this->service->hydrateEventFromInput($event, $_POST, $_FILES);

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
        $detailPages = $this->service->allYummyDetailPages();
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
            $this->service->hydrateEventFromInput($event, $_POST, $_FILES);
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

            $deleted = $this->service->deleteEvent($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'Yummy event could not be deleted.']);
            } else {
                Flash::setSuccess('Yummy event deleted successfully.');
            }
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
}
