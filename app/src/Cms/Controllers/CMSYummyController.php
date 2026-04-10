<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Models\YummyEvent;
use App\Services\YummyEventService;

final class CMSYummyController extends AbstractCmsEventController
{
    private YummyEventService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new YummyEventService();
    }

    protected function getService(): object
    {
        return $this->service;
    }

    protected function getViewPrefix(): string
    {
        return 'cms/yummy_event';
    }

    protected function getRoutePrefix(): string
    {
        return '/cms/events/yummy';
    }

    protected function getEventTypeName(): string
    {
        return 'Yummy';
    }

    protected function createEmptyEvent(): object
    {
        return new YummyEvent([
            'event_id' => 0,
            'event_type' => 'yummy',
            'thumbnail_path' => null,
        ]);
    }

    public function index(): void
    {
        \App\Utils\AdminGuard::requireAdmin(true);

        $events = $this->service->allEvents();
        $errors = \App\Utils\Flash::getErrors();
        $flashSuccess = \App\Utils\Flash::getSuccess();
        $csrfToken = \App\Utils\Csrf::token();

        require __DIR__ . '/../../Views/cms/yummy_events_index.php';
    }

    protected function handleImageUpload(object $event, bool $replaceOld): void
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

    protected function deleteEventImage(object $event): void
    {
        if (!empty($event->thumbnail_path)) {
            $this->uploads->deleteImage($event->thumbnail_path, 'yummy', 'event');
        }
    }
}
