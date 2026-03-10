<?php

namespace App\Repositories\Interfaces;

interface IOrderRepository
{
    public function findPendingOrderByUserId(int $userId): ?array;

    public function createPendingOrder(int $userId): int;

    public function addOrIncrementOrderItem(int $orderId, int $eventId): void;

    /** @return array<int, array<string, mixed>> */
    public function getOrderItemsWithEventData(int $orderId): array;

    public function removeOrderItem(int $orderId, int $orderItemId): bool;

    public function countItems(int $orderId): int;

    public function deleteOrder(int $orderId): void;

    public function findEventById(int $eventId): ?array;
}
