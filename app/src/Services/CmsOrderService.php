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
