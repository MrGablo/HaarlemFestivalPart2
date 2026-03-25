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

    public function findByName(string $name): ?Venue
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT venue_id, name FROM Venue WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name)) LIMIT 1'
        );
        $stmt->execute([':name' => $name]);

        $row = $stmt->fetch();
        return $row ? new Venue($row) : null;
    }

    public function createVenue(string $name): int
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO Venue (name) VALUES (:name)'
        );
        $stmt->execute([':name' => $name]);

        return (int)$this->getConnection()->lastInsertId();
    }

    public function updateVenue(int $venueId, string $name): void
    {
        $stmt = $this->getConnection()->prepare(
            'UPDATE Venue SET name = :name WHERE venue_id = :id'
        );
        $stmt->execute([
            ':name' => $name,
            ':id' => $venueId,
        ]);
    }

    public function deleteVenue(int $venueId): bool
    {
        $stmt = $this->getConnection()->prepare(
            'DELETE FROM Venue WHERE venue_id = :id'
        );
        $stmt->execute([':id' => $venueId]);

        return $stmt->rowCount() > 0;
    }

    public function countJazzEventsUsingVenue(int $venueId): int
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT COUNT(*) FROM JazzEvent WHERE venue_id = :id'
        );
        $stmt->execute([':id' => $venueId]);

        return (int)$stmt->fetchColumn();
    }
}
