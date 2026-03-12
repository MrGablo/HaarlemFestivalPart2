<?php

namespace App\Models;

class Artist
{
    public int $artist_id;
    public string $name;
    public ?int $page_id;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $row)
    {
        $this->artist_id = (int)($row['artist_id'] ?? 0);
        $this->name = (string)($row['name'] ?? '');
        $this->page_id = isset($row['page_id']) ? (int)$row['page_id'] : null;
        $this->created_at = isset($row['created_at']) ? (string)$row['created_at'] : null;
        $this->updated_at = isset($row['updated_at']) ? (string)$row['updated_at'] : null;
    }
}
