<?php

declare(strict_types=1);

namespace App\Cms\Controllers;

use App\Models\JazzEvent;
use App\Services\JazzEventService;

final class CMSJazzController extends AbstractCmsEventController
{
    private JazzEventService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new JazzEventService();
    }

    protected function getService(): object
    {
        return $this->service;
    }

    protected function getViewPrefix(): string
    {
        return 'cms/jazz_event';
    }

    protected function getRoutePrefix(): string
    {
        return '/cms/events/jazz';
    }

    protected function getEventTypeName(): string
    {
        return 'Jazz';
    }

    protected function createEmptyEvent(): object
    {
        return new JazzEvent([
            'event_id' => 0,
            'event_type' => 'jazz',
            'img_background' => null,
        ]);
    }

    protected function getViewData(): array
    {
        return [
            'artists' => $this->service->allArtists(),
            'venues' => $this->service->allVenues(),
        ];
    }

    public function index(): void
    {
        // Jazz index view uses 'cms/jazz_events_index.php' not 'cms/jazz_event_index.php'
        \App\Utils\AdminGuard::requireAdmin(true);

        $events = $this->service->allEvents();
        $errors = \App\Utils\Flash::getErrors();
        $flashSuccess = \App\Utils\Flash::getSuccess();
        $csrfToken = \App\Utils\Csrf::token();

        require __DIR__ . '/../../Views/cms/jazz_events_index.php';
    }

    protected function handleImageUpload(object $event, bool $replaceOld): void
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
            'jazz',
            'event',
            null,
            false,
            $replaceOld ? $event->img_background : null
        );
    }

    protected function deleteEventImage(object $event): void
    {
        if (!empty($event->img_background)) {
            $this->uploads->deleteImage($event->img_background, 'jazz', 'event');
        }
    }
}
