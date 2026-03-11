<?php

namespace App\Services;

use App\Models\JazzEvent;
use App\Repositories\ArtistRepository;
use App\Repositories\JazzEventRepository;
use App\Repositories\PageRepository;

class CmsJazzEventService
{
    public function __construct(
        private JazzEventRepository $events = new JazzEventRepository(),
        private ArtistRepository $artists = new ArtistRepository(),
        private PageRepository $pages = new PageRepository()
    ) {}

    public function allEvents(): array { return $this->events->getAllJazzEvents(); }
    public function allArtists(): array { return $this->artists->getAllArtists(); }
    public function allPages(): array { return $this->pages->getAllPages(); }
    public function findEvent(int $id): ?JazzEvent { return $this->events->findJazzEventById($id); }
    public function createEvent(JazzEvent $event): int { return $this->events->createJazzEvent($event); }
    public function updateEvent(JazzEvent $event): void { $this->events->updateJazzEvent($event); }
    public function deleteEvent(int $id): bool { return $this->events->deleteJazzEventById($id); }
    public function artistExists(int $id): bool { return $this->artists->findArtistById($id) !== null; }
}
