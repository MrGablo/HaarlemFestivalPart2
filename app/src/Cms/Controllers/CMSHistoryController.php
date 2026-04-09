<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Models\HistoryEvent;
use App\Services\HistoryEventService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSHistoryController
{
    private HistoryEventService $service;

    public function __construct()
    {
        $this->service = new HistoryEventService();
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $events = $this->service->allEvents();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/history_events_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);

        $old = Flash::getOld();
        $detailPages = $this->service->allHistoryDetailPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/history_event_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $event = new HistoryEvent([
                'event_id' => 0,
                'event_type' => 'history',
            ]);
            $this->service->hydrateEventFromInput($event, $_POST);

            $newId = $this->service->createEvent($event);
            Flash::setSuccess('History event created successfully.');
            header('Location: /cms/events/history/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
            header('Location: /cms/events/history/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->getEventOrRedirect($id);
        $detailPages = $this->service->allHistoryDetailPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/history_event_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->getEventOrRedirect($id);

        try {
            Csrf::assertPost();
            $this->service->hydrateEventFromInput($event, $_POST);
            $this->service->updateEvent($event);
            Flash::setSuccess('History event updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/history/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $deleted = $this->service->deleteEvent($id);
            if (!$deleted) {
                Flash::setErrors(['general' => 'History event could not be deleted.']);
            } else {
                Flash::setSuccess('History event deleted successfully.');
            }
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: /cms/events/history', true, 302);
        exit;
    }

    private function getEventOrRedirect(int $id): HistoryEvent
    {
        $event = $this->service->findEvent($id);
        if ($event !== null) {
            return $event;
        }

        Flash::setErrors(['general' => 'History event not found.']);
        header('Location: /cms/events/history', true, 302);
        exit;
    }
}