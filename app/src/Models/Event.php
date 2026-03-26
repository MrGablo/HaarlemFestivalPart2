<?php

namespace App\Models;

abstract class Event
{
        public int $event_id;
        public string $title;
        public string $event_type;
        public int $availability;

        public function __construct(array $row)
        {
                $this->event_id = (int)($row['event_id'] ?? 0);
                $this->title = (string)($row['title'] ?? '');
                $this->event_type = (string)($row['event_type'] ?? '');
                $this->availability = (int)($row['availability'] ?? 300);
        }
}
