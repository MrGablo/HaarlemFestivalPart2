<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\HistoryEvent;

interface IHistoryEventRepository
{
    /** @return HistoryEvent[] */
    public function getAllHistoryEvents(): array;

    public function findHistoryEventById(int $eventId): ?HistoryEvent;

    public function createHistoryEvent(HistoryEvent $event): int;

    public function updateHistoryEvent(HistoryEvent $event): void;

    public function deleteHistoryEventById(int $eventId): bool;
}