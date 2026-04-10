<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\JazzEvent;
use App\Repositories\ArtistRepository;
use App\Repositories\JazzEventRepository;
use App\Repositories\PageRepository;
use App\Repositories\VenueRepository;

class JazzEventService
{
    public function __construct(
        private JazzEventRepository $events = new JazzEventRepository(),
        private ArtistRepository $artists = new ArtistRepository(),
        private PageRepository $pages = new PageRepository(),
        private VenueRepository $venues = new VenueRepository(),
        private EventValidationService $validator = new EventValidationService()
    ) {}

    public function allEvents(): array
    {
        return $this->events->getAllJazzEvents();
    }

    public function allArtists(): array
    {
        return $this->artists->getAllArtists();
    }

    public function allPages(): array
    {
        return $this->pages->getAllPages();
    }

    public function allVenues(): array
    {
        return $this->venues->getAllVenues();
    }

    public function findEvent(int $id): ?JazzEvent
    {
        return $this->events->findJazzEventById($id);
    }

    public function hydrateEventFromInput(JazzEvent $event, array $input): void
    {
        $event->title = $this->validator->requireText($input, 'title', 'Title');
        $event->start_date = $this->validator->normalizeDateTime((string)($input['start_date'] ?? ''));
        $event->end_date = $this->validator->normalizeDateTime((string)($input['end_date'] ?? ''));
        $event->venue_id = $this->validator->parseRequiredPositiveInt((string)($input['venue_id'] ?? ''), 'Venue');
        $event->location = '';
        $event->artist_id = $this->validator->parseRequiredPositiveInt((string)($input['artist_id'] ?? ''), 'Artist');

        if ($this->venues->findById((int)$event->venue_id) === null) {
            throw new \RuntimeException('Selected venue does not exist.');
        }

        if (!$this->artistExists($event->artist_id)) {
            throw new \RuntimeException('Selected artist does not exist.');
        }

        $event->price = $this->validator->parsePrice((string)($input['price'] ?? ''));
        $event->page_id = $this->validator->parseOptionalPositiveInt((string)($input['page_id'] ?? ''), 'Page ID');
    }

    public function createEvent(JazzEvent $event): int
    {
        return $this->events->createJazzEvent($event);
    }

    public function updateEvent(JazzEvent $event): void
    {
        $this->events->updateJazzEvent($event);
    }

    public function deleteEvent(int $id): bool
    {
        return $this->events->deleteJazzEventById($id);
    }

    public function artistExists(int $id): bool
    {
        return $this->artists->findArtistById($id) !== null;
    }

    public function assignPageToArtistEvents(int $artistId, int $pageId): void
    {
        $this->events->assignPageToArtistEvents($artistId, $pageId);
    }
}
