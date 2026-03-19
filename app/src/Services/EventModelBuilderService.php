<?php

namespace App\Services;

use App\Models\DanceEvent;
use App\Models\Event;
use App\Models\GenericEvent;
use App\Models\JazzEvent;

class EventModelBuilderService
{
    public function buildEventModel(array $row): Event
    {
        $eventType = strtolower((string) ($row['event_type'] ?? ''));

        return match ($eventType) {
            'jazz' => $this->buildJazzEvent($row),
            'dance' => $this->buildDanceEvent($row),
            default => $this->buildGenericEvent($row),
        };
    }

    private function buildJazzEvent(array $row): JazzEvent
    {
        return new JazzEvent($row);
    }

    private function buildDanceEvent(array $row): DanceEvent
    {
        return new DanceEvent($row);
    }

    private function buildGenericEvent(array $row): GenericEvent
    {
        return new GenericEvent($row);
    }
}
