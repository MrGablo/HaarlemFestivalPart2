<?php

declare(strict_types=1);

namespace App\Cms\Services;

use App\Cms\Models\OrderExportRequest;
use App\Cms\Models\OrderExportResult;
use App\Repositories\OrderRepository;

final class CmsOrderExportService
{
    public function __construct(
        private CmsOrderService $orderService = new CmsOrderService(),
        private OrderRepository $orders = new OrderRepository(),
        private OrderExportRowBuilder $rowBuilder = new OrderExportRowBuilder()
    ) {}

    /** @return array<string, string> */
    public function getExportColumns(): array
    {
        return [
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'customer_name' => 'Customer Name',
            'customer_email' => 'Customer Email',
            'status' => 'Status',
            'order_created_at' => 'Order Created At',
            'order_item_id' => 'Order Item ID',
            'event_id' => 'Event ID',
            'event_title' => 'Event Title',
            'event_type' => 'Event Type',
            'artist_name' => 'Artist',
            'venue_name' => 'Venue',
            'event_start_date' => 'Event Start Date',
            'quantity' => 'Quantity',
            'unit_price' => 'Price Per Item',
            'line_total' => 'Line Total',
            'order_total' => 'Order Total',
        ];
    }

    /** @return array<string, string> */
    public function getAvailableExportColumnsForOrder(int $orderId): array
    {
        if ($orderId <= 0) {
            return [];
        }

        $rows = $this->buildScopedRows('order', $orderId, 0, '', '', 'created_at', 'DESC');
        return $this->rowBuilder->availableColumnsForRows($this->getExportColumns(), $rows);
    }

    public function prepareExport(OrderExportRequest $request): OrderExportResult
    {
        $rows = $this->buildScopedRows(
            $request->scope,
            $request->orderId,
            $request->userId,
            $request->criteria->search,
            $request->criteria->statusFilter,
            $request->criteria->sortColumn,
            $request->criteria->sortDirection
        );

        if ($rows === []) {
            throw new \RuntimeException('No order rows available to export.');
        }

        $columnMap = $this->getExportColumns();
        if ($request->scope === 'order' && $request->orderId > 0) {
            $available = $this->getAvailableExportColumnsForOrder($request->orderId);
            $columnMap = $available;
            $columns = $this->rowBuilder->normalizeColumns($request->columns, $available, array_keys($available));
        } else {
            $columns = $this->rowBuilder->normalizeColumns($request->columns, $columnMap, array_keys($columnMap));
        }

        $safeRows = [];
        foreach ($rows as $exportRow) {
            $row = [];
            foreach ($columns as $column) {
                if (!array_key_exists($column, $columnMap)) {
                    continue;
                }

                $row[$columnMap[$column]] = (string)($exportRow[$column] ?? '');
            }
            $safeRows[] = $row;
        }

        if (in_array($request->scope, ['all', 'user'], true) && $safeRows !== []) {
            $statusTotals = $this->calculateStatusTotals(
                $request->scope,
                $request->userId,
                $request->criteria->search,
                $request->criteria->statusFilter,
                $request->criteria->sortColumn,
                $request->criteria->sortDirection
            );
            $summaryRows = $this->rowBuilder->buildStatusSummaryRows($columns, $columnMap, $statusTotals);
            foreach ($summaryRows as $summaryRow) {
                $safeRows[] = $summaryRow;
            }
        }

        return new OrderExportResult($safeRows, $columns, $columnMap, $request->format);
    }

    /** @return array{payed: float, pending: float, general: float} */
    private function calculateStatusTotals(
        string $scope,
        int $userId,
        string $search,
        string $statusFilter,
        string $sortColumn,
        string $sortDirection
    ): array {
        $totals = [
            'payed' => 0.0,
            'pending' => 0.0,
            'general' => 0.0,
        ];

        $orders = $this->orderService->searchOrders($search, $statusFilter, $sortColumn, $sortDirection);
        foreach ($orders as $summary) {
            $summaryUserId = (int)($summary['user_id'] ?? 0);
            if ($scope === 'user' && $userId > 0 && $summaryUserId !== $userId) {
                continue;
            }

            $amount = (float)($summary['total_amount'] ?? 0.0);
            $status = strtolower((string)($summary['status'] ?? 'pending'));

            if ($status === 'payed') {
                $totals['payed'] += $amount;
            } else {
                $totals['pending'] += $amount;
            }

            $totals['general'] += $amount;
        }

        return $totals;
    }

    /** @return array<int, array<string, string>> */
    private function buildScopedRows(
        string $scope,
        int $orderId,
        int $userId,
        string $search,
        string $statusFilter,
        string $sortColumn,
        string $sortDirection
    ): array {
        $scope = strtolower(trim($scope));
        if (!in_array($scope, ['all', 'user', 'order'], true)) {
            $scope = 'all';
        }

        if ($scope === 'order' && $orderId <= 0) {
            throw new \RuntimeException('Please provide a valid order ID for single-order export.');
        }

        if ($scope === 'user' && $userId <= 0) {
            throw new \RuntimeException('Please provide a valid user ID for user export.');
        }

        $orders = $this->orderService->searchOrders($search, $statusFilter, $sortColumn, $sortDirection);

        if ($scope === 'order') {
            $orders = array_values(array_filter(
                $orders,
                fn(array $order): bool => (int)($order['order_id'] ?? 0) === $orderId
            ));
        }

        if ($scope === 'user') {
            $orders = array_values(array_filter(
                $orders,
                fn(array $order): bool => (int)($order['user_id'] ?? 0) === $userId
            ));
        }

        if ($orders === []) {
            throw new \RuntimeException('No orders found for the selected export scope.');
        }

        $orderIds = array_map(fn(array $o): int => (int)($o['order_id'] ?? 0), $orders);
        $orderIds = array_values(array_filter($orderIds, fn(int $id): bool => $id > 0));

        $allItems = $this->orders->getOrderItemsForOrders($orderIds);
        return $this->rowBuilder->buildRows($orders, $allItems);
    }
}
