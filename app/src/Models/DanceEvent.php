<?php

declare(strict_types=1);

namespace App\Models;

/** Dance ticket: price, venue id, location name. */
final class DanceEvent extends Event
{
    public ?int $venue_id;
    public string $location;
    public float $price;

    public function __construct(array $row)
    {
        parent::__construct($row);
        if ($this->event_type === '') {
            $this->event_type = 'dance';
        }
        $vid = $row['venue_id'] ?? $row['location_id'] ?? null;
        $this->venue_id = $vid !== null && $vid !== '' ? (int) $vid : null;
        $this->location = (string) ($row['location'] ?? '');
        $this->price = (float) ($row['price'] ?? 0);
    }
}
