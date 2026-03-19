<?php

namespace App\Repositories\Interfaces;

use App\Models\Artist;

interface IArtistRepository
{
    /** @return Artist[] */
    public function getAllArtists(): array;

    public function findArtistById(int $artistId): ?Artist;

    public function createArtist(Artist $artist): int;

    public function updateArtist(Artist $artist): void;

    public function deleteArtistById(int $artistId): bool;
}
