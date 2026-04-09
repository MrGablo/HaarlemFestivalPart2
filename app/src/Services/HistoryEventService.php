<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HistoryEvent;
use App\Repositories\HistoryEventRepository;
use App\Repositories\PageRepository;

class HistoryEventService
{
    public function __construct(
        private HistoryEventRepository $events = new HistoryEventRepository(),
        private PageRepository $pages = new PageRepository()
    ) {}

    public function allEvents(): array
    {
        return $this->events->getAllHistoryEvents();
    }

    public function findEvent(int $id): ?HistoryEvent
    {
        return $this->events->findHistoryEventById($id);
    }

    public function createEvent(HistoryEvent $event): int
    {
        return $this->events->createHistoryEvent($event);
    }

    public function updateEvent(HistoryEvent $event): void
    {
        $this->events->updateHistoryEvent($event);
    }

    public function deleteEvent(int $id): bool
    {
        return $this->events->deleteHistoryEventById($id);
    }

    public function allHistoryDetailPages(): array
    {
        return $this->pages->getPagesByType('History_Detail_Page');
    }

    public function hydrateEventFromInput(HistoryEvent $event, array $input): void
    {
        $event->title = $this->requireText($input, 'title', 'Title');
        $event->availability = $this->parseAvailability((string)($input['availability'] ?? ''));
        $event->language = $this->parseLanguage((string)($input['language'] ?? ''));
        $event->start_date = $this->normalizeDateTime((string)($input['start_date'] ?? ''));
        $event->location = $this->requireText($input, 'location', 'Location');
        $event->price = $this->parsePrice((string)($input['price'] ?? ''));
        $event->family_price = $this->parsePrice((string)($input['family_price'] ?? ''), 'Family price');
    }

    private function requireText(array $input, string $key, string $label): string
    {
        $raw = trim((string)($input[$key] ?? ''));
        if ($raw === '') {
            throw new \RuntimeException($label . ' is required.');
        }

        return $raw;
    }

    private function parseAvailability(string $raw): int
    {
        $raw = trim($raw);
        if ($raw === '' || !ctype_digit($raw)) {
            throw new \RuntimeException('Availability must be a whole number.');
        }

        return (int)$raw;
    }

    private function parseLanguage(string $raw): string
    {
        $value = strtoupper(trim($raw));
        if (!in_array($value, ['NL', 'EN', 'CH'], true)) {
            throw new \RuntimeException('Language must be one of NL, EN, or CH.');
        }

        return $value;
    }

    private function parsePrice(string $raw, string $label = 'Price'): float
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

    private function normalizeDateTime(string $input): string
    {
        $input = trim($input);
        if ($input === '') {
            throw new \RuntimeException('Start date is required.');
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