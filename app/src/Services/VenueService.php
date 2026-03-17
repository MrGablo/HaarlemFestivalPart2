<?php

namespace App\Services;

use App\Models\Venue;
use App\Repositories\Interfaces\IVenueRepository;

class VenueService
{
    public function __construct(private IVenueRepository $venueRepo) {}

    /** @return Venue[] */
    public function getAllVenues(): array
    {
        return $this->venueRepo->getAllVenues();
    }

    /**
     * Returns venues shaped for API responses.
     * @return array{venue_id:int,name:string,display_name:string}[]
     */
    public function getVenuesForApi(): array
    {
        return array_map(fn(Venue $v) => [
            'venue_id'     => $v->venue_id,
            'name'         => $v->name,
            'display_name' => $v->displayName(),
        ], $this->venueRepo->getAllVenues());
    }
}
