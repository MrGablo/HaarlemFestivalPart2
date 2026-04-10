<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use PDO;

interface IEventRepository
{
    public function getAllEvents(?string $eventType = null): array;
    public function findEventById(int $eventId): ?array;
    public function updateEvent(int $eventId, string $title, int $availability): bool;
    public function decrementAvailabilityByOne(int $eventId): bool;
    public function decrementAvailabilityByOneUsingConnection(PDO $pdo, int $eventId): bool;
}
