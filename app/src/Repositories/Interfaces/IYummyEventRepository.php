<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\YummyEvent;

interface IYummyEventRepository
{
    /**
     * @return YummyEvent[]
     */
    public function getAllYummyEvents(): array;

    public function getEventDetails(int $eventId): ?YummyEvent;

    public function getSessionsForYummyEvent(int $eventId): array;

    /**
     * @return YummyEvent[]
     */
    public function getAllYummyEventsForCMS(): array;

    public function findYummyEventById(int $eventId): ?YummyEvent;

    public function createYummyEvent(YummyEvent $event): int;

    public function updateYummyEvent(YummyEvent $event): void;

    public function deleteYummyEventById(int $eventId): bool;
}
