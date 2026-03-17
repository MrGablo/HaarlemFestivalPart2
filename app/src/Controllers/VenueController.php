<?php

namespace App\Controllers;

use App\Repositories\VenueRepository;
use App\Services\VenueService;

class VenueController
{
    private VenueService $venueService;

    public function __construct()
    {
        $this->venueService = new VenueService(new VenueRepository());
    }

    /** GET /api/venues — returns all venues as JSON. */
    public function list(): void
    {
        $venues = $this->venueService->getVenuesForApi();

        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'venues' => $venues]);
        exit;
    }
}
