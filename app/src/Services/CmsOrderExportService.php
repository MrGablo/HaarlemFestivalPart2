<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepository;

final class CmsOrderExportService
{
    public function __construct(
        private CmsOrderService $orderService,
        private OrderRepository $orders = new OrderRepository()
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
        $allColumns = $this->getExportColumns();

        // Build export-shaped rows for this order and keep only columns that have at least one non-empty value.
        $orderRows = $this->buildExportRows('order', $orderId, 0);
        if ($orderRows === []) {
            return [];
        }

        $available = [];
        foreach ($allColumns as $key => $label) {
            foreach ($orderRows as $row) {
                $value = (string)($row[$key] ?? '');
                if (trim($value) !== '') {
                    $available[$key] = $label;
                    break;
                }
            }
        }

        return $available;
    }

    /** @return array<int, string> */
    public function normalizeSelectedColumns(mixed $columns): array
    {
        $availableKeys = array_keys($this->getExportColumns());
        if (!is_array($columns) || $columns === []) {
            return $availableKeys;
        }

        $selected = [];
        foreach ($columns as $column) {
            $key = trim((string)$column);
            if ($key !== '' && in_array($key, $availableKeys, true) && !in_array($key, $selected, true)) {
                $selected[] = $key;
            }
        }

        return $selected !== [] ? $selected : $availableKeys;
    }

    /** @return array<int, array<string, string>> */
    public function buildExportRows(
        string $scope,
        int $orderId,
        int $userId,
        string $search = '',
        string $statusFilter = '',
        string $sortColumn = 'created_at',
        string $sortDirection = 'DESC'
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

        // Collect all order IDs and index summaries, then fetch all items in one query (avoids N+1)
        $orderIds = array_map(fn(array $o): int => (int)($o['order_id'] ?? 0), $orders);
        $orderIds = array_values(array_filter($orderIds, fn(int $id): bool => $id > 0));

        $allItems = $this->orders->getOrderItemsForOrders($orderIds);

        // Group items by order_id.
        $itemsByOrder = [];
        foreach ($allItems as $itemRow) {
            $oid = (int)($itemRow['order_id'] ?? 0);
            $itemsByOrder[$oid][] = $itemRow;
        }

        // Index summaries keyed by order_id so we can look them up below.
        $summaryByOrder = [];
        foreach ($orders as $orderSummary) {
            $oid = (int)($orderSummary['order_id'] ?? 0);
            $summaryByOrder[$oid] = $orderSummary;
        }

        $rows = [];
        foreach ($orderIds as $oid) {
            $orderSummary = $summaryByOrder[$oid] ?? null;
            if ($orderSummary === null) {
                continue;
            }

            $rawItems = $itemsByOrder[$oid] ?? [];

            // Calculate order total and build normalized item list from the raw DB rows.
            $items = [];
            $calculatedTotal = 0.0;
            foreach ($rawItems as $row) {
                $quantity = max(0, (int)($row['quantity'] ?? 0));
                $unitPrice = (float)($row['price'] ?? 0.0);
                $lineTotal = $quantity * $unitPrice;
                $calculatedTotal += $lineTotal;

                $items[] = [
                    'order_item_id' => (int)($row['order_item_id'] ?? 0),
                    'event_id' => (int)($row['event_id'] ?? 0),
                    'title' => (string)($row['title'] ?? 'Event'),
                    'event_type' => (string)($row['event_type'] ?? ''),
                    'artist_name' => (string)($row['artist_name'] ?? ''),
                    'venue_name' => (string)($row['venue_name'] ?? ''),
                    'start_date' => (string)($row['start_date'] ?? ''),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $orderTotal = $calculatedTotal;

            if ($items === []) {
                $rows[] = [
                    'order_id' => (string)($orderSummary['order_id'] ?? ''),
                    'user_id' => (string)($orderSummary['user_id'] ?? ''),
                    'customer_name' => (string)($orderSummary['customer_name'] ?? ''),
                    'customer_email' => (string)($orderSummary['customer_email'] ?? ''),
                    'status' => (string)($orderSummary['status'] ?? ''),
                    'order_created_at' => (string)($orderSummary['created_at'] ?? ''),
                    'order_item_id' => '',
                    'event_id' => '',
                    'event_title' => '',
                    'event_type' => '',
                    'artist_name' => '',
                    'venue_name' => '',
                    'event_start_date' => '',
                    'quantity' => '0',
                    'unit_price' => number_format(0, 2, '.', ''),
                    'line_total' => number_format(0, 2, '.', ''),
                    'order_total' => number_format($orderTotal, 2, '.', ''),
                ];

                continue;
            }

            foreach ($items as $item) {
                $unitPrice = (float)($item['unit_price'] ?? 0.0);
                $lineTotal = (float)($item['line_total'] ?? 0.0);

                $rows[] = [
                    'order_id' => (string)($orderSummary['order_id'] ?? ''),
                    'user_id' => (string)($orderSummary['user_id'] ?? ''),
                    'customer_name' => (string)($orderSummary['customer_name'] ?? ''),
                    'customer_email' => (string)($orderSummary['customer_email'] ?? ''),
                    'status' => (string)($orderSummary['status'] ?? ''),
                    'order_created_at' => (string)($orderSummary['created_at'] ?? ''),
                    'order_item_id' => (string)($item['order_item_id'] ?? ''),
                    'event_id' => (string)($item['event_id'] ?? ''),
                    'event_title' => (string)($item['title'] ?? ''),
                    'event_type' => (string)($item['event_type'] ?? ''),
                    'artist_name' => (string)($item['artist_name'] ?? ''),
                    'venue_name' => (string)($item['venue_name'] ?? ''),
                    'event_start_date' => (string)($item['start_date'] ?? ''),
                    'quantity' => (string)($item['quantity'] ?? 0),
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
                    'line_total' => number_format($lineTotal, 2, '.', ''),
                    'order_total' => number_format($orderTotal, 2, '.', ''),
                ];
            }
        }

        if ($rows === []) {
            throw new \RuntimeException('No order rows available to export.');
        }

        return $rows;
    }
}
