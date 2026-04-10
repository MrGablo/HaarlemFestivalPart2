<?php

declare(strict_types=1);

namespace App\Cms\Models;

final class OrderSearchCriteria
{
    public function __construct(
        public string $search = '',
        public string $statusFilter = '',
        public string $sortColumn = 'created_at',
        public string $sortDirection = 'DESC'
    ) {}

    /** @param array<string, mixed> $input */
    public static function fromArray(array $input): self
    {
        $sortDirection = strtoupper(trim((string)($input['dir'] ?? 'DESC')));
        if (!in_array($sortDirection, ['ASC', 'DESC'], true)) {
            $sortDirection = 'DESC';
        }

        return new self(
            search: trim((string)($input['search'] ?? '')),
            statusFilter: trim((string)($input['status'] ?? '')),
            sortColumn: trim((string)($input['sort'] ?? 'created_at')),
            sortDirection: $sortDirection
        );
    }
}
