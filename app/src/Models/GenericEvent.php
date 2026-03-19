<?php

namespace App\Models;

class GenericEvent extends Event
{
    public ?float $price;
    public string $location;

    public function __construct(array $row)
    {
        parent::__construct($row);
        $this->price = isset($row['price']) ? (float)$row['price'] : null;
        $this->location = (string)($row['location'] ?? '');
    }
}
