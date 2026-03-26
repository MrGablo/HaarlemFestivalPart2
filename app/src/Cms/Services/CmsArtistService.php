<?php

namespace App\Services;

use App\Models\Artist;
use App\Repositories\ArtistRepository;
use App\Repositories\PageRepository;

class CmsArtistService
{
    public function __construct(
        private ArtistRepository $artists = new ArtistRepository(),
        private PageRepository $pages = new PageRepository()
    ) {}

    public function allArtists(): array { return $this->artists->getAllArtists(); }
    public function allPages(): array { return $this->pages->getAllPages(); }
    public function findArtist(int $id): ?Artist { return $this->artists->findArtistById($id); }
    public function createArtist(Artist $artist): int { return $this->artists->createArtist($artist); }
    public function updateArtist(Artist $artist): void { $this->artists->updateArtist($artist); }
    public function deleteArtist(int $id): bool { return $this->artists->deleteArtistById($id); }
}
