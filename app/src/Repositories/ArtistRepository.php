<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\Artist;
use PDO;

class ArtistRepository extends Repository
{
    /** @return Artist[] */
    public function getAllArtists(): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->query(
            'SELECT a.artist_id, a.name, a.page_id, p.Page_Title AS page_title
             FROM Artist a
             LEFT JOIN Page p ON p.Page_ID = a.page_id
             ORDER BY a.name ASC, a.artist_id ASC'
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(fn(array $row) => new Artist($row), $rows);
    }

    public function findArtistById(int $artistId): ?Artist
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            'SELECT a.artist_id, a.name, a.page_id, p.Page_Title AS page_title
             FROM Artist a
             LEFT JOIN Page p ON p.Page_ID = a.page_id
             WHERE a.artist_id = :artist_id
             LIMIT 1'
        );
        $stmt->execute([':artist_id' => $artistId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Artist($row) : null;
    }

    public function findArtistByPageId(int $pageId): ?Artist
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            'SELECT a.artist_id, a.name, a.page_id, p.Page_Title AS page_title
             FROM Artist a
             LEFT JOIN Page p ON p.Page_ID = a.page_id
             WHERE a.page_id = :page_id
             LIMIT 1'
        );
        $stmt->execute([':page_id' => $pageId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Artist($row) : null;
    }

    public function createArtist(Artist $artist): int
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            'INSERT INTO Artist (name, page_id, created_at, updated_at)
             VALUES (:name, :page_id, NOW(), NOW())'
        );
        $stmt->execute([
            ':name' => $artist->name,
            ':page_id' => $artist->page_id,
        ]);

        return (int)$pdo->lastInsertId();
    }

    public function updateArtist(Artist $artist): void
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare(
            'UPDATE Artist
             SET name = :name,
                 page_id = :page_id,
                 updated_at = NOW()
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
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare('DELETE FROM Artist WHERE artist_id = :artist_id');
        $stmt->execute([':artist_id' => $artistId]);

        return $stmt->rowCount() > 0;
    }
}