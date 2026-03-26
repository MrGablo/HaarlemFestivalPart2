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
}
