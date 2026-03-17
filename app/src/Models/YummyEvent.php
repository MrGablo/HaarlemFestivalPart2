<?php

namespace App\Models;

class YummyEvent extends Event
{
    public float $price;
    public string $cuisine;
    public ?string $thumbnail_path;
    public int $star_rating;

    public function __construct(array $row)
    {
        parent::__construct($row);
        if ($this->event_type === '') {
            $this->event_type = 'yummy';
        }

        $this->price = isset($row['price']) ? (float)$row['price'] : 0.0;
        $this->cuisine = (string)($row['cuisine'] ?? '');
        $this->thumbnail_path = isset($row['thumbnail_path']) ? (string)$row['thumbnail_path'] : null;
        $this->star_rating = isset($row['star_rating']) ? (int)$row['star_rating'] : 0;
    }
}
