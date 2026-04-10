<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\YummyEvent;
use App\Repositories\YummyEventRepository;
use App\Repositories\PageRepository;

class YummyEventService
{
    private YummyEventRepository $events;
    private PageRepository $pages;

    public function __construct()
    {
        $this->events = new YummyEventRepository();
        $this->pages = new PageRepository();
    }

    public function allEvents(): array
    {
        return $this->events->getAllYummyEventsForCMS();
    }

    public function findEvent(int $id): ?YummyEvent
    {
        return $this->events->findYummyEventById($id);
    }

    public function createEvent(YummyEvent $event): int
    {
        return $this->events->createYummyEvent($event);
    }

    public function updateEvent(YummyEvent $event): void
    {
        $this->events->updateYummyEvent($event);
    }

    public function deleteEvent(int $id): bool
    {
        return $this->events->deleteYummyEventById($id);
    }

    public function allYummyDetailPages(): array
    {
        return $this->pages->getPagesByType('Yummy_Detail_Page');
    }

    public function hydrateEventFromInput(YummyEvent $event, array $input, ?array $files = null): void
    {
        $event->title = $this->requireText($input, 'title', 'Title');
        $event->availability = $this->parseInt($input, 'availability', 'Availability');
        $event->cuisine = $this->requireText($input, 'cuisine', 'Cuisine');
        $event->star_rating = (int)($input['star_rating'] ?? 0);
        if ($event->star_rating < 1 || $event->star_rating > 5) {
            throw new \RuntimeException('Star rating must be between 1 and 5.');
        }

        $event->start_time = $this->normalizeDateTime((string)($input['start_time'] ?? ''), 'Start time');
        $event->end_time = $this->normalizeDateTime((string)($input['end_time'] ?? ''), 'End time');

        $event->price = $this->parsePrice((string)($input['price'] ?? ''), 'Price');

        $pageId = trim((string)($input['page_id'] ?? ''));
        $event->page_id = ($pageId !== '') ? (int)$pageId : null;

        if ($files !== null && isset($files['thumbnail_path_file']) && $files['thumbnail_path_file']['error'] === UPLOAD_ERR_OK) {
            $imageSrc = $this->uploadImage($files['thumbnail_path_file']);
            if ($imageSrc) {
                $event->thumbnail_path = $imageSrc;
            }
        }
    }

    private function uploadImage(array $file): ?string
    {
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new \RuntimeException('Image exceeds maximum size of 5MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            throw new \RuntimeException('Invalid image format.');
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };

        $filename = md5(uniqid('', true)) . '.' . $ext;
        $relativePath = '/assets/img/yummy/events/' . $filename;
        $destPath = __DIR__ . '/../../public' . $relativePath;

        if (!is_dir(dirname($destPath))) {
            mkdir(dirname($destPath), 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            return $relativePath;
        }

        throw new \RuntimeException('Failed to save uploaded image.');
    }

    private function requireText(array $input, string $key, string $label): string
    {
        $raw = trim((string)($input[$key] ?? ''));
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        return $raw;
    }

    private function parseInt(array $input, string $key, string $label): int
    {
        $raw = trim((string)($input[$key] ?? ''));
        if ($raw === '' || !ctype_digit($raw)) {
            throw new \RuntimeException($label . ' must be a whole number.');
        }

        return (int)$raw;
    }

    private function parsePrice(string $raw, string $label): float
    {
        $raw = trim($raw);
        if ($raw === '' || !is_numeric($raw)) {
            throw new \RuntimeException($label . ' must be numeric.');
        }

        $price = (float)$raw;
        if ($price < 0) {
            throw new \RuntimeException($label . ' cannot be negative.');
        }

        return $price;
    }

    private function normalizeDateTime(string $input, string $label): string
    {
        $input = trim($input);
        if ($input === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $input)) {
            return str_replace('T', ' ', $input) . ':00';
        }

        try {
            $dt = new \DateTime($input);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw new \RuntimeException('Invalid date/time format for ' . $label . '.');
        }
    }
}
