<?php

namespace App\Models;

class Artist
{
    public int $artist_id;
    public string $name;
    public ?int $page_id;
    public ?string $page_title;

    public function __construct(array $row)
    {
        $this->artist_id = (int)($row['artist_id'] ?? 0);
        $this->name = (string)($row['name'] ?? '');
        $this->page_id = isset($row['page_id']) && $row['page_id'] !== null ? (int)$row['page_id'] : null;
        $this->page_title = isset($row['page_title']) ? (string)$row['page_title'] : null;
    }
}