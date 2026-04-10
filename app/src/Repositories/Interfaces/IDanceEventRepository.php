<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\DanceEvent;

interface IDanceEventRepository
{
    /** @return DanceEvent[] */
    public function getAllDanceEvents(): array;
    public function findDanceEventById(int $eventId): ?DanceEvent;
    public function createDanceEvent(DanceEvent $event): int;
    public function updateDanceEvent(DanceEvent $event): void;
    public function deleteDanceEventById(int $eventId): bool;
}
