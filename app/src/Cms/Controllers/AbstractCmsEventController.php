<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Services\UploadService;
use App\Utils\AdminGuard;
use App\Utils\Csrf;
use App\Utils\Flash;
use App\Utils\Session;

abstract class AbstractCmsEventController
{
    protected UploadService $uploads;

    public function __construct()
    {
        $this->uploads = new UploadService();
        Session::ensureStarted();
    }

    /**
     * @return object The specific event service
     */
    abstract protected function getService(): object;

    /**
     * @return string The base view path without .php (e.g. 'cms/jazz_events')
     */
    abstract protected function getViewPrefix(): string;

    /**
     * @return string The prefix for CMS routes (e.g. '/cms/events/jazz')
     */
    abstract protected function getRoutePrefix(): string;

    /**
     * @return string The human readable event type (e.g. 'Jazz')
     */
    abstract protected function getEventTypeName(): string;

    /**
     * @return object A new empty event model instance
     */
    abstract protected function createEmptyEvent(): object;

    /**
     * Additional data to be passed to createForm/edit views.
     */
    protected function getViewData(): array
    {
        return [];
    }

    abstract protected function handleImageUpload(object $event, bool $replaceOld): void;
    abstract protected function deleteEventImage(object $event): void;

    public function index(): void
    {
        AdminGuard::requireAdmin(true);

        $events = $this->getService()->allEvents();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        require __DIR__ . '/../../Views/' . $this->getViewPrefix() . '_index.php';
    }

    public function createForm(): void
    {
        AdminGuard::requireAdmin(true);

        $old = Flash::getOld();
        $pages = $this->getService()->allPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        extract($this->getViewData());

        require __DIR__ . '/../../Views/' . $this->getViewPrefix() . '_create.php';
    }

    public function create(): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $event = $this->createEmptyEvent();
            $this->getService()->hydrateEventFromInput($event, $_POST);
            $this->handleImageUpload($event, false);

            $newId = $this->getService()->createEvent($event);
            Flash::setSuccess($this->getEventTypeName() . ' event created successfully.');
            header('Location: ' . $this->getRoutePrefix() . '/' . $newId, true, 302);
            exit;
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
            Flash::setOld($_POST);
            header('Location: ' . $this->getRoutePrefix() . '/create', true, 302);
            exit;
        }
    }

    public function edit(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->getEventOrRedirect($id);

        $pages = $this->getService()->allPages();
        $errors = Flash::getErrors();
        $flashSuccess = Flash::getSuccess();
        $csrfToken = Csrf::token();

        extract($this->getViewData());

        require __DIR__ . '/../../Views/' . $this->getViewPrefix() . '_edit.php';
    }

    public function update(int $id): void
    {
        AdminGuard::requireAdmin(true);

        $event = $this->getEventOrRedirect($id);

        try {
            Csrf::assertPost();

            $this->getService()->hydrateEventFromInput($event, $_POST);
            $this->handleImageUpload($event, true);

            $this->getService()->updateEvent($event);
            Flash::setSuccess($this->getEventTypeName() . ' event updated successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: ' . $this->getRoutePrefix() . '/' . $id, true, 302);
        exit;
    }

    public function delete(int $id): void
    {
        AdminGuard::requireAdmin(true);

        try {
            Csrf::assertPost();

            $event = $this->getEventOrRedirect($id);

            $deleted = $this->getService()->deleteEvent($id);
            if (!$deleted) {
                Flash::setErrors(['general' => $this->getEventTypeName() . ' event could not be deleted.']);
                header('Location: ' . $this->getRoutePrefix(), true, 302);
                exit;
            }

            $this->deleteEventImage($event);
            Flash::setSuccess($this->getEventTypeName() . ' event deleted successfully.');
        } catch (\Throwable $e) {
            Flash::setErrors(['general' => $e->getMessage()]);
        }

        header('Location: ' . $this->getRoutePrefix(), true, 302);
        exit;
    }

    private function getEventOrRedirect(int $id): object
    {
        $event = $this->getService()->findEvent($id);
        if ($event !== null) {
            return $event;
        }

        Flash::setErrors(['general' => $this->getEventTypeName() . ' event not found.']);
        header('Location: ' . $this->getRoutePrefix(), true, 302);
        exit;
    }
}
