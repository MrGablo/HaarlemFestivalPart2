<?php

declare(strict_types=1);

namespace App\Cms\Models;

final class OrderExportRequest
{
    /** @param array<int, string> $columns */
    public function __construct(
        public OrderSearchCriteria $criteria,
        public string $format = 'csv',
        public string $scope = 'all',
        public int $userId = 0,
        public int $orderId = 0,
        public array $columns = []
    ) {}

    /** @param array<string, mixed> $input */
    public static function fromArray(array $input): self
    {
        $format = strtolower(trim((string)($input['format'] ?? 'csv')));
        if (!in_array($format, ['csv', 'excel'], true)) {
            $format = 'csv';
        }

        $scope = strtolower(trim((string)($input['scope'] ?? 'all')));
        if (!in_array($scope, ['all', 'user', 'order'], true)) {
            $scope = 'all';
        }

        $userId = isset($input['user_id']) ? max(0, (int)$input['user_id']) : 0;
        $orderId = isset($input['order_id']) ? max(0, (int)$input['order_id']) : 0;

        $columns = [];
        $rawColumns = $input['columns'] ?? [];
        if (is_array($rawColumns)) {
            foreach ($rawColumns as $column) {
                $columns[] = trim((string)$column);
            }
        }

        return new self(
            criteria: OrderSearchCriteria::fromArray($input),
            format: $format,
            scope: $scope,
            userId: $userId,
            orderId: $orderId,
            columns: $columns
        );
    }

    /** @return array<string, int|string|null> */
    public function toRedirectQueryParams(): array
    {
        return [
            'search' => $this->criteria->search,
            'status' => $this->criteria->statusFilter,
            'sort' => $this->criteria->sortColumn,
            'dir' => $this->criteria->sortDirection,
            'scope' => $this->scope,
            'user_id' => $this->userId > 0 ? $this->userId : null,
            'order_id' => $this->orderId > 0 ? $this->orderId : null,
        ];
    }
}
