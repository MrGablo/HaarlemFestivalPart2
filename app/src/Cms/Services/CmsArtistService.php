<?php

namespace App\Cms\Services;

use App\Models\Artist;
use App\Services\ArtistService;

class CmsArtistService
{
    public function __construct(
        private ArtistService $service = new ArtistService()
    ) {}

    public function allArtists(): array { return $this->service->allArtists(); }
    public function allPages(): array { return $this->service->allPages(); }
    public function findArtist(int $id): ?Artist { return $this->service->findArtist($id); }
    public function assignPageToArtist(int $artistId, int $pageId): void { $this->service->assignPageToArtist($artistId, $pageId); }
    public function createArtist(Artist $artist): int { return $this->service->createArtist($artist); }
    public function updateArtist(Artist $artist): void { $this->service->updateArtist($artist); }
    public function deleteArtist(int $id): bool { return $this->service->deleteArtist($id); }
}
