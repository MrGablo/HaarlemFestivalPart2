<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

interface IDanceHomeRepository
{
    public function findDanceArtistEventsByPageId(int $pageId): array;
    public function findDanceArtistEventsByArtistName(string $artistName): array;
    // Sessions at one venue (see DanceHomeRepository).
    public function findDanceLocationEventsByVenueId(int $venueId): array;
    public function findDanceTimetableRows(): array;
    public function findDanceLineupHeadlines(int $limit = 6): array;
    public function findDanceLineupArtists(int $limit = 6): array;
    public function getDanceSessionEventIdsByDate(string $isoDate): array;
    public function getDanceSessionEventIdsByPassEvent(int $passEventId): array;
    public function getAllDanceSessionEventIds(): array;
    public function getDanceCoveredSessionEventIdsByEventId(int $eventId): array;
}
