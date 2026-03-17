<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrderStatus;
use App\Repositories\OrderRepository;

final class CmsOrderService
{
    public function __construct(
        private OrderRepository $orders = new OrderRepository()
    ) {}

    /** @return array<int, string> */
    public function getStatuses(): array
    {
        return [OrderStatus::PENDING->value, OrderStatus::PAYED->value];
    }

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

    /** @return array<int, array<string, mixed>> */
    public function searchOrders(string $search = '', string $statusFilter = '', string $sortColumn = 'created_at', string $sortDirection = 'DESC'): array
    {
        $rows = $this->orders->getAllOrdersWithSummary();
        $normalized = array_map(fn(array $row): array => $this->normalizeSummaryRow($row), $rows);

        if ($search !== '' || $statusFilter !== '') {
            $normalized = array_values(array_filter(
                $normalized,
                fn(array $row): bool => $this->matchesFilters($row, $search, $statusFilter)
            ));
        }

        $sortBy = $this->normalizeSortColumn($sortColumn);
        $sortDir = strtoupper($sortDirection) === 'ASC' ? 1 : -1;

        usort($normalized, function (array $a, array $b) use ($sortBy, $sortDir): int {
            $left = $a[$sortBy] ?? null;
            $right = $b[$sortBy] ?? null;

            if (is_string($left)) {
                $left = strtolower($left);
            }
            if (is_string($right)) {
                $right = strtolower($right);
            }

            return ($left <=> $right) * $sortDir;
        });

        return $normalized;
    }

    /** @return array<string, mixed>|null */
    public function findOrderDetails(int $orderId): ?array
    {
        if ($orderId <= 0) {
            return null;
        }

        $summaryRow = $this->orders->findOrderSummaryById($orderId);
        if ($summaryRow === null) {
            return null;
        }

        $order = $this->normalizeSummaryRow($summaryRow);
        $itemsRows = $this->orders->getOrderItemsWithEventData($orderId);

        $items = [];
        $calculatedItemCount = 0;
        $calculatedTotal = 0.0;

        foreach ($itemsRows as $row) {
            $quantity = max(0, (int)($row['quantity'] ?? 0));
            $unitPrice = (float)($row['price'] ?? 0.0);
            $lineTotal = $quantity * $unitPrice;

            $calculatedItemCount += $quantity;
            $calculatedTotal += $lineTotal;

            $items[] = [
                'order_item_id' => (int)($row['order_item_id'] ?? 0),
                'event_id' => (int)($row['event_id'] ?? 0),
                'title' => (string)($row['title'] ?? 'Event'),
                'event_type' => (string)($row['event_type'] ?? ''),
                'artist_name' => (string)($row['artist_name'] ?? ''),
                'venue_name' => (string)($row['venue_name'] ?? ''),
                'start_date' => (string)($row['start_date'] ?? ''),
                'end_date' => (string)($row['end_date'] ?? ''),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'created_at' => (string)($row['order_item_created_at'] ?? ''),
            ];
        }

        $order['item_count'] = $calculatedItemCount;
        $order['total_amount'] = $calculatedTotal;
        $order['paid_amount'] = $order['status'] === OrderStatus::PAYED->value ? $calculatedTotal : 0.0;

        return [
            'order' => $order,
            'items' => $items,
        ];
    }

    /** @param array<string, mixed> $quantitiesByItemId */
    public function updateOrder(int $orderId, string $status, array $quantitiesByItemId): void
    {
        $details = $this->findOrderDetails($orderId);
        if ($details === null) {
            throw new \RuntimeException('Order not found.');
        }

        $normalizedStatus = strtolower(trim($status));
        if (!in_array($normalizedStatus, $this->getStatuses(), true)) {
            throw new \RuntimeException('Invalid order status.');
        }

        $allowedItemIds = [];
        foreach (($details['items'] ?? []) as $item) {
            $itemId = (int)($item['order_item_id'] ?? 0);
            if ($itemId > 0) {
                $allowedItemIds[] = $itemId;
            }
        }

        $updated = $this->orders->updateOrderStatus($orderId, $normalizedStatus);
        if (!$updated) {
            throw new \RuntimeException('Order status could not be updated.');
        }

        foreach ($quantitiesByItemId as $rawItemId => $rawQuantity) {
            $orderItemId = (int)$rawItemId;
            if (!in_array($orderItemId, $allowedItemIds, true)) {
                continue;
            }

            $quantity = (int)$rawQuantity;
            if ($quantity < 0) {
                throw new \RuntimeException('Quantities cannot be negative.');
            }

            if ($quantity === 0) {
                $this->orders->removeOrderItem($orderId, $orderItemId);
                continue;
            }

            $itemUpdated = $this->orders->updateOrderItemQuantity($orderId, $orderItemId, $quantity);
            if (!$itemUpdated) {
                throw new \RuntimeException('One of the order items could not be updated.');
            }
        }
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

        $orders = $this->searchOrders($search, $statusFilter, $sortColumn, $sortDirection);

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

        // Collect all order IDs and index summaries — then fetch all items in one query (avoids N+1)
        $orderIds = array_map(fn(array $o): int => (int)($o['order_id'] ?? 0), $orders);
        $orderIds = array_values(array_filter($orderIds, fn(int $id): bool => $id > 0));

        $allItems = $this->orders->getOrderItemsForOrders($orderIds);

        // Group items by order_id
        $itemsByOrder = [];
        foreach ($allItems as $itemRow) {
            $oid = (int)($itemRow['order_id'] ?? 0);
            $itemsByOrder[$oid][] = $itemRow;
        }

        // Index summaries keyed by order_id so we can look them up below
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

            // Calculate order total and build normalized item list from the raw DB rows
            $items = [];
            $calculatedTotal = 0.0;
            foreach ($rawItems as $row) {
                $quantity = max(0, (int)($row['quantity'] ?? 0));
                $unitPrice = (float)($row['price'] ?? 0.0);
                $lineTotal = $quantity * $unitPrice;
                $calculatedTotal += $lineTotal;

                $items[] = [
                    'order_item_id' => (int)($row['order_item_id'] ?? 0),
                    'event_id'      => (int)($row['event_id'] ?? 0),
                    'title'         => (string)($row['title'] ?? 'Event'),
                    'event_type'    => (string)($row['event_type'] ?? ''),
                    'artist_name'   => (string)($row['artist_name'] ?? ''),
                    'venue_name'    => (string)($row['venue_name'] ?? ''),
                    'start_date'    => (string)($row['start_date'] ?? ''),
                    'quantity'      => $quantity,
                    'unit_price'    => $unitPrice,
                    'line_total'    => $lineTotal,
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

    /** @param array<string, mixed> $row */
    private function normalizeSummaryRow(array $row): array
    {
        $firstName = trim((string)($row['first_name'] ?? ''));
        $lastName = trim((string)($row['last_name'] ?? ''));
        $status = strtolower((string)($row['order_status'] ?? OrderStatus::PENDING->value));
        if (!in_array($status, $this->getStatuses(), true)) {
            $status = OrderStatus::PENDING->value;
        }

        $total = (float)($row['total_amount'] ?? 0.0);

        return [
            'order_id' => (int)($row['order_id'] ?? 0),
            'user_id' => (int)($row['user_id'] ?? 0),
            'customer_name' => trim($firstName . ' ' . $lastName) !== '' ? trim($firstName . ' ' . $lastName) : 'Unknown user',
            'customer_email' => (string)($row['email'] ?? ''),
            'status' => $status,
            'item_count' => (int)($row['item_count'] ?? 0),
            'total_amount' => $total,
            'paid_amount' => $status === OrderStatus::PAYED->value ? $total : 0.0,
            'created_at' => (string)($row['created_at'] ?? ''),
        ];
    }

    /** @param array<string, mixed> $row */
    private function matchesFilters(array $row, string $search, string $statusFilter): bool
    {
        $statusFilter = strtolower(trim($statusFilter));
        if ($statusFilter !== '' && ($row['status'] ?? '') !== $statusFilter) {
            return false;
        }

        $search = strtolower(trim($search));
        if ($search === '') {
            return true;
        }

        return str_contains(strtolower((string)($row['customer_name'] ?? '')), $search)
            || str_contains(strtolower((string)($row['customer_email'] ?? '')), $search)
            || str_contains(strtolower((string)($row['status'] ?? '')), $search)
            || str_contains((string)($row['order_id'] ?? ''), $search)
            || str_contains((string)($row['user_id'] ?? ''), $search);
    }

    private function normalizeSortColumn(string $sortColumn): string
    {
        $allowed = [
            'order_id',
            'user_id',
            'customer_name',
            'customer_email',
            'status',
            'item_count',
            'total_amount',
            'paid_amount',
            'created_at',
        ];

        return in_array($sortColumn, $allowed, true) ? $sortColumn : 'created_at';
    }
}
