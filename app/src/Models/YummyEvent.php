<?php

namespace App\Models;

class YummyEvent extends Event
{
    public int $id;
    public float $price;
    public string $cuisine;
    public ?string $thumbnail_path;
    public int $star_rating;
    public ?int $page_id;
    public string $start_time;
    public string $end_time;

    public function __construct(array $row)
    {
        parent::__construct($row);
        $this->id = (int)($row['yummy_id'] ?? $row['id'] ?? 0);
        if ($this->event_type === '') {
            $this->event_type = 'yummy';
        }

        $this->price = isset($row['price']) ? (float)$row['price'] : 0.0;
        $this->cuisine = (string)($row['cuisine'] ?? '');
        $this->thumbnail_path = isset($row['thumbnail_path']) ? (string)$row['thumbnail_path'] : null;
        $this->star_rating = isset($row['star_rating']) ? (int)$row['star_rating'] : 0;
        $this->page_id = isset($row['page_id']) ? (int)$row['page_id'] : null;
        $this->start_time = (string)($row['start_time'] ?? '');
        $this->end_time = (string)($row['end_time'] ?? '');
    }
}
