<?php

namespace App\Repositories\Interfaces;

use App\Models\Venue;

interface IVenueRepository
{
    /** @return Venue[] */
    public function getAllVenues(): array;

    public function findById(int $venueId): ?Venue;

    public function findByName(string $name): ?Venue;

    public function createVenue(string $name): int;

    public function updateVenue(int $venueId, string $name): void;

    public function deleteVenue(int $venueId): bool;

    public function countJazzEventsUsingVenue(int $venueId): int;
}
