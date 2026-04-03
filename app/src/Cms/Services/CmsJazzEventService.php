<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Models\JazzEvent;
use App\Repositories\ArtistRepository;
use App\Repositories\JazzEventRepository;
use App\Repositories\PageRepository;
use App\Repositories\VenueRepository;

class CmsJazzEventService
{
    public function __construct(
        private JazzEventRepository $events = new JazzEventRepository(),
        private ArtistRepository $artists = new ArtistRepository(),
        private PageRepository $pages = new PageRepository(),
        private VenueRepository $venues = new VenueRepository()
    ) {}

    public function allEvents(): array { return $this->events->getAllJazzEvents(); }
    public function allArtists(): array { return $this->artists->getAllArtists(); }
    public function allPages(): array { return $this->pages->getAllPages(); }
    public function allVenues(): array { return $this->venues->getAllVenues(); }
    public function findEvent(int $id): ?JazzEvent { return $this->events->findJazzEventById($id); }
    public function hydrateEventFromInput(JazzEvent $event, array $input): void
    {
        $event->title = $this->requireText($input, 'title', 'Title');
        $event->start_date = $this->normalizeDateTime((string)($input['start_date'] ?? ''));
        $event->end_date = $this->normalizeDateTime((string)($input['end_date'] ?? ''));
        $event->venue_id = $this->parseRequiredPositiveInt((string)($input['venue_id'] ?? ''), 'Venue');
        $event->location = '';
        $event->artist_id = $this->parseRequiredPositiveInt((string)($input['artist_id'] ?? ''), 'Artist');

        if ($this->venues->findById((int)$event->venue_id) === null) {
            throw new \RuntimeException('Selected venue does not exist.');
        }

        if (!$this->artistExists($event->artist_id)) {
            throw new \RuntimeException('Selected artist does not exist.');
        }

        $event->price = $this->parsePrice((string)($input['price'] ?? ''));
        $event->page_id = $this->parseOptionalPositiveInt((string)($input['page_id'] ?? ''), 'Page ID');
    }

    public function createEvent(JazzEvent $event): int { return $this->events->createJazzEvent($event); }
    public function updateEvent(JazzEvent $event): void { $this->events->updateJazzEvent($event); }
    public function deleteEvent(int $id): bool { return $this->events->deleteJazzEventById($id); }
    public function artistExists(int $id): bool { return $this->artists->findArtistById($id) !== null; }
    public function assignPageToArtistEvents(int $artistId, int $pageId): void { $this->events->assignPageToArtistEvents($artistId, $pageId); }

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
