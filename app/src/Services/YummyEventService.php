<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\YummyEvent;
use App\Repositories\PageRepository;
use App\Repositories\YummyEventRepository;

class YummyEventService
{
    public function __construct(
        private YummyEventRepository $events = new YummyEventRepository(),
        private PageRepository $pages = new PageRepository()
    ) {}

    public function allEvents(): array
    {
        return $this->events->getAllCMSYummyEvents();
    }

    public function allPages(): array
    {
        $pages = $this->pages->getAllPages();
        // Filter to only show Yummy detail pages
        return array_filter($pages, function ($page) {
            return ($page['Page_Type'] ?? '') === 'Yummy_Detail_Page';
        });
    }

    public function findEvent(int $id): ?YummyEvent
    {
        return $this->events->findYummyEventById($id);
    }

    public function hydrateEventFromInput(YummyEvent $event, array $input): void
    {
        $event->title = $this->requireText($input, 'title', 'Title');
        $event->availability = $this->parseRequiredPositiveInt((string)($input['availability'] ?? '0'), 'Availability');
        $event->start_time = $this->normalizeDateTime((string)($input['start_time'] ?? ''));
        $event->end_time = $this->normalizeDateTime((string)($input['end_time'] ?? ''));
        $event->cuisine = $this->requireText($input, 'cuisine', 'Cuisine');
        $event->star_rating = (int)($input['star_rating'] ?? 0);

        if ($event->star_rating < 0 || $event->star_rating > 5) {
            throw new \RuntimeException('Star rating must be between 0 and 5.');
        }

        $event->price = $this->parsePrice((string)($input['price'] ?? ''));
        $event->page_id = $this->parseOptionalPositiveInt((string)($input['page_id'] ?? ''), 'Page ID');
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

    private function requireText(array $input, string $key, string $label): string
    {
        $raw = trim((string)($input[$key] ?? ''));
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        return $raw;
    }

    private function parsePrice(string $raw): float
    {
        $raw = trim($raw);
        if ($raw === '') {
            throw new \RuntimeException('Price is required.');
        }

        if (!is_numeric($raw)) {
            throw new \RuntimeException('Price must be numeric.');
        }

        $price = (float)$raw;
        if ($price < 0) {
            throw new \RuntimeException('Price cannot be negative.');
        }

        return $price;
    }

    private function parseOptionalPositiveInt(string $raw, string $label): ?int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $value = (int)$raw;
        if ($value <= 0) {
            throw new \RuntimeException($label . ' must be a positive integer.');
        }

        return $value;
    }

    private function parseRequiredPositiveInt(string $raw, string $label): int
    {
        $raw = trim($raw);
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        $value = (int)$raw;
        if ($value <= 0) {
            throw new \RuntimeException($label . ' must be a positive integer.');
        }

        return $value;
    }

    private function normalizeDateTime(string $input): string
    {
        $input = trim($input);

        if ($input === '') {
            throw new \RuntimeException('Date/time fields are required.');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $input)) {
            return str_replace('T', ' ', $input) . ':00';
        }

        try {
            $dt = new \DateTime($input);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw new \RuntimeException('Invalid date/time format.');
        }
    }
}