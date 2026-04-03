<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Services\EventService;

class CmsEventService
{
    public function __construct(
        private EventService $service = new EventService()
    ) {}

    public function getAllowedEventTypes(): array
    {
        return $this->service->getAllowedEventTypes();
    }

    public function normalizeFilterType(mixed $rawType): ?string
    {
        return $this->service->normalizeFilterType($rawType);
    }

    public function allEvents(?string $eventType): array
    {
        return $this->service->allEvents($eventType);
    }

    public function findEvent(int $eventId): ?array
    {
        return $this->service->findEvent($eventId);
    }

    public function updateEventFromInput(int $eventId, array $input): void
    {
        $this->service->updateEventFromInput($eventId, $input);
    }
}
