<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\Venue;
use App\Repositories\Interfaces\IVenueRepository;

class VenueRepository extends Repository implements IVenueRepository
{
    /** @return Venue[] */
    public function getAllVenues(): array
    {
        $stmt = $this->getConnection()->query(
            'SELECT venue_id, name FROM Venue ORDER BY venue_id ASC'
        );

        $rows = $stmt->fetchAll() ?: [];
        return array_map(fn(array $r) => new Venue($r), $rows);
    }

    public function findById(int $venueId): ?Venue
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT venue_id, name FROM Venue WHERE venue_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $venueId]);

        $row = $stmt->fetch();
        return $row ? new Venue($row) : null;
    }
}
