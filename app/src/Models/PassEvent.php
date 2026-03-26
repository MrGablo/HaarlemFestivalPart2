<?php

namespace App\Models;

final class PassEvent
{
    public function __construct(
        public int $event_id,
        public string $festival_type,
        public string $pass_scope,
        public float $base_price,
        public string $title,
        public bool $active
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int)($row['event_id'] ?? 0),
            strtolower((string)($row['festival_type'] ?? '')),
            strtolower((string)($row['pass_scope'] ?? '')),
            (float)($row['base_price'] ?? 0),
            (string)($row['title'] ?? ''),
            (int)($row['active'] ?? 0) === 1
        );
    }

    public function requiresDaySelection(): bool
    {
        return $this->pass_scope === 'day';
    }
}
