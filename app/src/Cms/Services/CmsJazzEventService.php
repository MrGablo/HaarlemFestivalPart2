<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Models\JazzEvent;
use App\Services\JazzEventService;

class CmsJazzEventService
{
    public function __construct(
        private JazzEventService $service = new JazzEventService()
    ) {}

    public function allEvents(): array { return $this->service->allEvents(); }
    public function allArtists(): array { return $this->service->allArtists(); }
    public function allPages(): array { return $this->service->allPages(); }
    public function allVenues(): array { return $this->service->allVenues(); }
    public function findEvent(int $id): ?JazzEvent { return $this->service->findEvent($id); }
    public function hydrateEventFromInput(JazzEvent $event, array $input): void
    {
        $this->service->hydrateEventFromInput($event, $input);
    }

    public function createEvent(JazzEvent $event): int { return $this->service->createEvent($event); }
    public function updateEvent(JazzEvent $event): void { $this->service->updateEvent($event); }
    public function deleteEvent(int $id): bool { return $this->service->deleteEvent($id); }
    public function artistExists(int $id): bool { return $this->service->artistExists($id); }
    public function assignPageToArtistEvents(int $artistId, int $pageId): void { $this->service->assignPageToArtistEvents($artistId, $pageId); }
}
