<?php

namespace App\Repositories\Interfaces;

use App\Models\JazzEvent;

interface IJazzEventRepository
{
    /** @return JazzEvent[] */
    public function getAllJazzEvents(): array;

    public function updateJazzEvent(JazzEvent $event): void;

    public function findJazzEventById(int $eventId): ?JazzEvent;
}