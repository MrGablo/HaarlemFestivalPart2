<?php

declare(strict_types=1);

namespace App\Models;

/** Dance ticket: price, venue id, location name. */
final class DanceEvent extends Event
{
    public string $start_date;
    public string $end_date;
    public string $event_date;
    public ?int $venue_id;
    public string $location;
    public string $venue_name;
    public ?int $artist_id;
    public string $artist_name;
    public ?string $img_background;
    public float $price;
    public ?int $page_id;
    public string $row_kind;
    public int $sort_order;
    public string $day_display_label;
    public ?string $session_tag;
    public bool $tag_special;

    public function __construct(array $row)
    {
        parent::__construct($row);
        if ($this->event_type === '') {
            $this->event_type = 'dance';
        }
        $vid = $row['venue_id'] ?? $row['location_id'] ?? null;
        $this->venue_id = $vid !== null && $vid !== '' ? (int) $vid : null;
        $this->start_date = (string) ($row['start_date'] ?? $row['session_start'] ?? '');
        $this->end_date = (string) ($row['end_date'] ?? $row['session_end'] ?? '');
        $this->event_date = (string) ($row['event_date'] ?? $row['day_display_label'] ?? '');
        $this->venue_name = (string) ($row['venue_name'] ?? $row['location_name'] ?? $row['location'] ?? '');
        $this->location = $this->venue_name !== '' ? $this->venue_name : (string)($row['location'] ?? '');
        $aid = $row['artist_id'] ?? null;
        $this->artist_id = $aid !== null && $aid !== '' ? (int)$aid : null;
        $this->artist_name = (string) ($row['artist_name'] ?? '');
        $this->img_background = isset($row['img_background']) ? (string)$row['img_background'] : null;
        $this->price = (float) ($row['price'] ?? 0);
        $pid = $row['page_id'] ?? null;
        $this->page_id = $pid !== null && $pid !== '' ? (int)$pid : null;
        $this->row_kind = (string) ($row['row_kind'] ?? 'session');
        $this->sort_order = (int) ($row['sort_order'] ?? 0);
        $this->day_display_label = (string) ($row['day_display_label'] ?? $this->event_date);
        $this->session_tag = isset($row['session_tag']) ? (string)$row['session_tag'] : null;
        $this->tag_special = !empty($row['tag_special']);
    }
}
