<?php

namespace App\Services;

use App\Models\Event;
use App\Models\GenericEvent;
use App\Models\JazzEvent;
use App\Models\YummyEvent;

class EventModelBuilderService
{
    public function buildEventModel(array $row): Event
    {
        $eventType = strtolower((string)($row['event_type'] ?? ''));

        return match ($eventType) {
            'jazz' => $this->buildJazzEvent($row),
            'yummy' => $this->buildYummyEvent($row),
            default => $this->buildGenericEvent($row),
        };
    }

    private function buildJazzEvent(array $row): JazzEvent
    {
        return new JazzEvent($row);
    }

    private function buildYummyEvent(array $row): YummyEvent
    {
        return new YummyEvent($row);
    }

    private function buildGenericEvent(array $row): GenericEvent
    {
        return new GenericEvent($row);
    }
}
