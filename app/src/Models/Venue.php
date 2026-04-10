<?php

namespace App\Models;

class Venue
{
    public int $venue_id;
    public string $name;

    public function __construct(array $row)
    {
        $this->venue_id = (int)($row['venue_id'] ?? 0);
        $this->name = (string)($row['name'] ?? '');
    }

    /** Display name used in hall-filter tabs: Grote Markt shows as "Free". */
    public function displayName(): string
    {
        return mb_strtolower(trim($this->name)) === 'grote markt' ? 'Free' : $this->name;
    }
}
