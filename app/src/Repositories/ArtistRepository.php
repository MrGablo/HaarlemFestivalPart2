<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\Artist;
use App\Repositories\Interfaces\IArtistRepository;

class ArtistRepository extends Repository implements IArtistRepository
{
    public function getAllArtists(): array
    {
        $rows = $this->getConnection()->query(
            'SELECT artist_id, name, page_id, created_at, updated_at
             FROM Artist
             ORDER BY name ASC'
        )->fetchAll() ?: [];

        return array_map(fn(array $row) => new Artist($row), $rows);
    }

    public function findArtistById(int $artistId): ?Artist
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT artist_id, name, page_id, created_at, updated_at
             FROM Artist
             WHERE artist_id = :artist_id
             LIMIT 1'
        );

        $stmt->execute([':artist_id' => $artistId]);
        $row = $stmt->fetch();

        return is_array($row) ? new Artist($row) : null;
    }

    public function createArtist(Artist $artist): int
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO Artist (name, page_id)
             VALUES (:name, :page_id)'
        );

        $stmt->execute([
            ':name' => $artist->name,
            ':page_id' => $artist->page_id,
        ]);

        return (int)$this->getConnection()->lastInsertId();
    }

    public function updateArtist(Artist $artist): void
    {
        $stmt = $this->getConnection()->prepare(
            'UPDATE Artist
             SET name = :name,
                 page_id = :page_id,
                 updated_at = CURRENT_TIMESTAMP
             WHERE artist_id = :artist_id'
        );

        $stmt->execute([
            ':name' => $artist->name,
            ':page_id' => $artist->page_id,
            ':artist_id' => $artist->artist_id,
        ]);
    }

    public function deleteArtistById(int $artistId): bool
    {
        $stmt = $this->getConnection()->prepare('DELETE FROM Artist WHERE artist_id = :artist_id');
        $stmt->execute([':artist_id' => $artistId]);

        return $stmt->rowCount() > 0;
    }

    public function assignPageToArtist(int $artistId, int $pageId): void
    {
        $stmt = $this->getConnection()->prepare(
            'UPDATE Artist
             SET page_id = :page_id,
                 updated_at = CURRENT_TIMESTAMP
             WHERE artist_id = :artist_id'
        );

        $stmt->execute([
            ':page_id' => $pageId,
            ':artist_id' => $artistId,
        ]);
    }
}
