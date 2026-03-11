<?php

namespace App\Models;

class JazzEvent extends Event
{
    public string $start_date;
    public string $end_date;

    public string $location;
    public ?int $artist_id;
    public string $artist_name;

    public ?string $img_background;
    public float $price;

    public ?int $page_id;

    /** Build from the associative array returned by PDO::FETCH_ASSOC */
    public function __construct(array $row)
    {
        parent::__construct($row);
        if ($this->event_type === '') {
            $this->event_type = 'jazz';
        }

        $this->start_date = (string)($row['start_date'] ?? '');
        $this->end_date = (string)($row['end_date'] ?? '');

        $this->location = (string)($row['location'] ?? '');
        $this->artist_id = isset($row['artist_id']) && $row['artist_id'] !== null ? (int)$row['artist_id'] : null;
        $this->artist_name = (string)($row['artist_name'] ?? '');

        $this->img_background = isset($row['img_background']) ? (string)$row['img_background'] : null;
        $this->price = (float)($row['price'] ?? 0);

        $this->page_id = isset($row['page_id']) && $row['page_id'] !== null ? (int)$row['page_id'] : null;
    }
}