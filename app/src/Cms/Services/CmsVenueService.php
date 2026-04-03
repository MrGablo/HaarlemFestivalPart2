<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Models\Venue;
use App\Services\VenueService;

class CmsVenueService
{
    public function __construct(private VenueService $service = new VenueService()) {}

    /** @return Venue[] */
    public function allVenues(): array
    {
        return $this->service->allVenues();
    }

    public function findVenue(int $venueId): ?Venue
    {
        return $this->service->findVenue($venueId);
    }

    public function createVenue(string $name): int
    {
        return $this->service->createVenue($name);
    }

    public function updateVenue(int $venueId, string $name): void
    {
        $this->service->updateVenue($venueId, $name);
    }

    public function deleteVenue(int $venueId): bool
    {
        return $this->service->deleteVenue($venueId);
    }

    public function countVenueUsage(int $venueId): int
    {
        return $this->service->countVenueUsage($venueId);
    }

    /** @return array<int,int> */
    public function getUsageByVenueId(): array
    {
        return $this->service->getUsageByVenueId();
    }
}
