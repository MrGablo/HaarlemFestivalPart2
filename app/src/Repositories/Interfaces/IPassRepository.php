<?php

namespace App\Repositories\Interfaces;

use App\Models\PassEvent;

interface IPassRepository
{
    /**
     * @return array<int, PassEvent>
     */
    public function getActivePassProductsByFestivalType(string $festivalType): array;

    public function findActivePassProductByEventId(int $eventId): ?PassEvent;

    /** @return array<int, string> */
    public function getAvailableJazzPassDates(): array;

    /** @return array<int, int> */
    public function getJazzEventIdsByDate(string $isoDate): array;

    /** @return array<int, int> */
    public function getAllJazzEventIds(): array;

    /** @return array<int, int> */
    public function getDanceSessionEventIdsByPassEvent(int $passEventId): array;

    /** @return array<int, int> */
    public function getAllDanceSessionEventIds(): array;
}
