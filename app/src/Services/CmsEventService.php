<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EventRepository;
enum EventTypes: string
    {
        case JAZZ = 'jazz';
        case STORIES = 'stories';
        case DANCE = 'dance';
    }
class CmsEventService
{
    

    public function __construct(
        private EventRepository $events = new EventRepository()
    ) {}

    public function getAllowedEventTypes(): array
    {
        return $values = array_map(fn($case) => $case->value, EventTypes::cases());
    }

    public function normalizeFilterType(mixed $rawType): ?string
    {
        if (!is_string($rawType)) {
            return null;
        }

        $type = strtolower(trim($rawType));
        if ($type === '') {
            return null;
        }

        return in_array($type, $this->getAllowedEventTypes(), true) ? $type : null;
    }

    public function allEvents(?string $eventType): array
    {
        return $this->events->getAllEvents($eventType);
    }

    public function findEvent(int $eventId): ?array
    {
        return $this->events->findEventById($eventId);
    }

    public function updateEventFromInput(int $eventId, array $input): void
    {
        $title = trim((string)($input['title'] ?? ''));
        if ($title === '') {
            throw new \RuntimeException('Event title is required.');
        }

        $availabilityRaw = trim((string)($input['availability'] ?? ''));
        if ($availabilityRaw === '' || !ctype_digit($availabilityRaw)) {
            throw new \RuntimeException('Availability must be a whole number.');
        }

        $availability = (int)$availabilityRaw;
        if ($availability < 0) {
            throw new \RuntimeException('Availability cannot be negative.');
        }

        $this->events->updateEvent($eventId, $title, $availability);
    }
}
