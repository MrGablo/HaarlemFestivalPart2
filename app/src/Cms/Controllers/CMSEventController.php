<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Cms\Services\CmsEventService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

final class CMSEventController
{
    private CmsEventService $service;

    public function __construct()
    {
        $this->service = new CmsEventService();
        Session::ensureStarted();
    }

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $selectedType = $this->service->normalizeFilterType($_GET['type'] ?? null);
        $events = $this->service->allEvents($selectedType);
        $allowedTypes = $this->service->getAllowedEventTypes();

        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();

        require __DIR__ . '/../../Views/cms/events_index.php';
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->service->findEvent($id);
        if ($event === null) {
            Flash::setErrors(['general' => 'Event not found.']);
            header('Location: /cms/events', true, 302);
            exit;
        }

        $selectedType = $this->service->normalizeFilterType($_GET['type'] ?? null);
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/cms/event_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $selectedType = $this->service->normalizeFilterType($_POST['return_type'] ?? null);

        try {
            Csrf::assertPost();

            $existing = $this->service->findEvent($id);
            if ($existing === null) {
                throw new \RuntimeException('Event not found.');
            }

            $this->service->updateEventFromInput($id, $_POST);
            Flash::setSuccess('Event updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        $suffix = $selectedType !== null ? '?type=' . urlencode($selectedType) : '';
        header('Location: /cms/events/' . $id . $suffix, true, 302);
        exit;
    }
}
