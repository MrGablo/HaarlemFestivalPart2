<?php

namespace App\Repositories\Interfaces;

use App\Models\Payment;

interface IPaymentRepository
{
    public function findPendingOrderByUserId(int $userId): ?Payment;
    public function markOrderAsPaid(int $orderId, int $userId): bool;
    public function isOrderPaid(int $orderId): bool;
    public function getOrderItemsByOrderId(int $orderId): array;
    public function getOrderDeliveryRecipient(int $orderId, int $userId): ?array;
    public function getInvoiceLineItems(int $orderId): array;
    public function getIssuedTicketsForOrder(int $orderId): array;
}
