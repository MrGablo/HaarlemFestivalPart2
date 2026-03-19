<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Venue;
use App\Repositories\VenueRepository;

class CmsVenueService
{
    public function __construct(private VenueRepository $venues = new VenueRepository()) {}

    /** @return Venue[] */
    public function allVenues(): array
    {
        return $this->venues->getAllVenues();
    }

    public function findVenue(int $venueId): ?Venue
    {
        return $this->venues->findById($venueId);
    }

    public function createVenue(string $name): int
    {
        $name = $this->normalizeName($name);
        $this->assertUniqueName($name);

        $venueId = $this->venues->createVenue($name);
        if ($venueId <= 0) {
            throw new \RuntimeException('Unable to create venue.');
        }

        return $venueId;
    }

    public function updateVenue(int $venueId, string $name): void
    {
        if ($venueId <= 0) {
            throw new \RuntimeException('Invalid venue id.');
        }

        $existing = $this->venues->findById($venueId);
        if ($existing === null) {
            throw new \RuntimeException('Venue not found.');
        }

        $name = $this->normalizeName($name);
        $duplicate = $this->venues->findByName($name);
        if ($duplicate !== null && $duplicate->venue_id !== $venueId) {
            throw new \RuntimeException('A venue with this name already exists.');
        }

        $this->venues->updateVenue($venueId, $name);
    }

    public function deleteVenue(int $venueId): bool
    {
        if ($venueId <= 0) {
            throw new \RuntimeException('Invalid venue id.');
        }

        $inUse = $this->venues->countJazzEventsUsingVenue($venueId);
        if ($inUse > 0) {
            throw new \RuntimeException('Venue is in use by jazz events. Reassign those events first.');
        }

        return $this->venues->deleteVenue($venueId);
    }

    public function countVenueUsage(int $venueId): int
    {
        if ($venueId <= 0) {
            return 0;
        }

        return $this->venues->countJazzEventsUsingVenue($venueId);
    }

    /** @return array<int,int> */
    public function getUsageByVenueId(): array
    {
        $usage = [];
        foreach ($this->allVenues() as $venue) {
            $usage[(int)$venue->venue_id] = $this->countVenueUsage((int)$venue->venue_id);
        }

        return $usage;
    }

    private function normalizeName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new \RuntimeException('Venue name is required.');
        }

        if (mb_strlen($name) > 120) {
            throw new \RuntimeException('Venue name must be 120 characters or fewer.');
        }

        return $name;
    }

    private function assertUniqueName(string $name): void
    {
        if ($this->venues->findByName($name) !== null) {
            throw new \RuntimeException('A venue with this name already exists.');
        }
    }
}
