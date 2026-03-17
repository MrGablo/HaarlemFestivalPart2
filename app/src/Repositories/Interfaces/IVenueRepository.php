<?php

namespace App\Repositories\Interfaces;

use App\Models\Venue;

interface IVenueRepository
{
    /** @return Venue[] */
    public function getAllVenues(): array;

    public function findById(int $venueId): ?Venue;
}
