<?php

declare(strict_types=1);

namespace App\Cms\Models;

final class OrderExportResult
{
    /**
     * @param array<int, array<string, string>> $rows
     * @param array<int, string> $columns
     * @param array<string, string> $columnMap
     */
    public function __construct(
        public array $rows,
        public array $columns,
        public array $columnMap,
        public string $format
    ) {}
}
