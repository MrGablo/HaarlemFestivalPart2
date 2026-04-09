<?php

namespace App\Repositories\Interfaces;

use App\Models\JazzEvent;

interface IJazzEventRepository
{
    /** @return JazzEvent[] */
    public function getAllJazzEvents(): array;

    public function createJazzEvent(JazzEvent $event): int;

    public function updateJazzEvent(JazzEvent $event): void;

    public function deleteJazzEventById(int $eventId): bool;

    public function findJazzEventById(int $eventId): ?JazzEvent;

    public function getJazzEventsByIds(array $eventIds): array; // JazzEvent[]

    /** @return JazzEvent[] */
    public function getJazzEventsByPageId(int $pageId): array;

    /** @return array<int, int> */
    public function getJazzEventIdsByDate(string $isoDate): array;

    /** @return array<int, int> */
    public function getAllJazzEventIds(): array;
}
