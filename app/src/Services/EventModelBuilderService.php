<?php

namespace App\Services;

use App\Models\Event;
use App\Models\JazzEvent;

class EventModelBuilderService
{
    public function buildEventModel(array $row): Event
    {
        $eventType = strtolower((string)($row['event_type'] ?? ''));

        return match ($eventType) {
            'jazz' => $this->buildJazzEvent($row),
            default => throw new \InvalidArgumentException('Unsupported event type: ' . $eventType),
        };
    }

    private function buildJazzEvent(array $row): JazzEvent
    {
        return new JazzEvent($row);
    }
}
