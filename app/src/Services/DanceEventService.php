<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DanceEvent;
use App\Repositories\ArtistRepository;
use App\Repositories\DanceEventRepository;
use App\Repositories\PageRepository;
use App\Repositories\VenueRepository;

final class DanceEventService
{
    public function __construct(
        private DanceEventRepository $events = new DanceEventRepository(),
        private ArtistRepository $artists = new ArtistRepository(),
        private PageRepository $pages = new PageRepository(),
        private VenueRepository $venues = new VenueRepository()
    ) {}

    public function allEvents(): array
    {
        return $this->events->getAllDanceEvents();
    }

    public function allArtists(): array
    {
        return $this->artists->getAllArtists();
    }

    public function allPages(): array
    {
        return array_values(array_filter(
            $this->pages->getAllPages(),
            static fn(array $p): bool => (string)($p['Page_Type'] ?? '') === 'Dance_Detail_Page'
        ));
    }

    public function allVenues(): array
    {
        return $this->venues->getAllVenues();
    }

    public function findEvent(int $id): ?DanceEvent
    {
        return $this->events->findDanceEventById($id);
    }

    public function hydrateEventFromInput(DanceEvent $event, array $input): void
    {
        $event->title = $this->requireText($input, 'title', 'Title');
        $event->start_date = $this->normalizeDateTime((string)($input['start_date'] ?? ''));
        $event->end_date = $this->normalizeDateTime((string)($input['end_date'] ?? ''));
        $event->event_date = substr($event->start_date, 0, 10);
        $event->day_display_label = $this->formatDayLabel($event->start_date);

        $event->venue_id = $this->parseRequiredPositiveInt((string)($input['venue_id'] ?? ''), 'Venue');
        if ($this->venues->findById((int)$event->venue_id) === null) {
            throw new \RuntimeException('Selected venue does not exist.');
        }

        $event->artist_id = $this->parseOptionalPositiveInt((string)($input['artist_id'] ?? ''), 'Artist');
        if ($event->artist_id !== null && $this->artists->findArtistById($event->artist_id) === null) {
            throw new \RuntimeException('Selected artist does not exist.');
        }

        $event->price = $this->parsePrice((string)($input['price'] ?? ''));
        $event->page_id = $this->parseOptionalPositiveInt((string)($input['page_id'] ?? ''), 'Linked page');
        $event->row_kind = 'session';
        $event->sort_order = (int)($input['sort_order'] ?? 999);
        $event->session_tag = (string)($input['session_tag'] ?? '');
        $event->tag_special = !empty($input['tag_special']);
    }

    public function createEvent(DanceEvent $event): int
    {
        return $this->events->createDanceEvent($event);
    }

    public function updateEvent(DanceEvent $event): void
    {
        $this->events->updateDanceEvent($event);
    }

    public function deleteEvent(int $id): bool
    {
        return $this->events->deleteDanceEventById($id);
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
        $value = $this->parseOptionalPositiveInt($raw, $label);
        if ($value === null) {
            throw new \RuntimeException($label . ' is required.');
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
        } catch (\Throwable) {
            throw new \RuntimeException('Invalid date/time format.');
        }
    }

    private function formatDayLabel(string $dateTime): string
    {
        try {
            $dt = new \DateTimeImmutable($dateTime);
            return $dt->format('l F jS');
        } catch (\Throwable) {
            return '';
        }
    }
}
