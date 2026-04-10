<?php

namespace App\Repositories\Interfaces;

interface IPaymentRepository
{
    public function findPendingOrderByUserId(int $userId): ?array;
    public function markOrderAsPaid(int $orderId, int $userId): bool;
    public function isOrderPaid(int $orderId): bool;
    public function getOrderItemsByOrderId(int $orderId): array;
    public function getOrderDeliveryRecipient(int $orderId, int $userId): ?array;
    public function getIssuedTicketsForOrder(int $orderId): array;
}
