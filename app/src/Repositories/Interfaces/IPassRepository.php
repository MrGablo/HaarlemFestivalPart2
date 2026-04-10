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
    /** @return array<int, string> */
    public function getAvailableDancePassDates(): array;

    /** @return array<int, array<string, mixed>> */
    public function getAllPassProducts(): array;

    /** @return array<string, mixed>|null */
    public function findPassProductByEventId(int $eventId): ?array;

    public function createPassProduct(string $title, string $festivalType, string $passScope, float $basePrice, bool $active): int;

    public function updatePassProduct(int $eventId, string $title, string $festivalType, string $passScope, float $basePrice, bool $active): void;

    public function deletePassProductByEventId(int $eventId): bool;
}
