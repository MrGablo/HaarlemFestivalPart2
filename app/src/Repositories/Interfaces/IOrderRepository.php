<?php

namespace App\Repositories\Interfaces;

interface IOrderRepository
{
    public function findPendingOrderByUserId(int $userId): ?array;

    /** @return array<int, array<string, mixed>> */
    public function getAllOrdersWithSummary(): array;

    public function findOrderSummaryById(int $orderId): ?array;

    public function updateOrderStatus(int $orderId, string $status): bool;

    public function createPendingOrder(int $userId): int;

    public function addOrIncrementOrderItem(int $orderId, int $eventId, int $quantity = 1, ?string $passDate = null): void;

    /** @return array<int, array<string, mixed>> */
    public function getOrderItemsWithEventData(int $orderId): array;

    /**
     * Fetch items (with event data) for multiple orders in a single query.
     *
     * @param  array<int, int>          $orderIds
     * @return array<int, array<string, mixed>>
     */
    public function getOrderItemsForOrders(array $orderIds): array;

    public function removeOrderItem(int $orderId, int $orderItemId): bool;

    public function updateOrderItemQuantity(int $orderId, int $orderItemId, int $quantity): bool;

    public function countItems(int $orderId): int;

    public function deleteOrder(int $orderId): void;

    public function findEventById(int $eventId): ?array;
}
