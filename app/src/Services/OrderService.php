<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Repositories\Interfaces\IOrderRepository;

class OrderService
{
    public function __construct(
        private IOrderRepository $orderRepository,
        private EventModelBuilderService $eventBuilder
    ) {}

    public function getPendingOrderForUser(int $userId): ?Order
    {
        if ($userId <= 0) {
            return null;
        }

        $orderRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($orderRow === null) {
            return null;
        }

        return $this->buildOrderFromRow($orderRow);
    }

    public function addEventToUserPendingOrder(int $userId, int $eventId): Order
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Invalid user id.');
        }

        if ($eventId <= 0) {
            throw new \InvalidArgumentException('Invalid event id.');
        }

        $eventRow = $this->orderRepository->findEventById($eventId);
        if ($eventRow === null) {
            throw new \RuntimeException('Event not found.');
        }

        // Validate event type can be mapped before storing the item.
        $this->eventBuilder->buildEventModel($eventRow);

        $orderRow = $this->orderRepository->findPendingOrderByUserId($userId);
        $orderId = $orderRow !== null
            ? (int)$orderRow['order_id']
            : $this->orderRepository->createPendingOrder($userId);

        $this->orderRepository->addOrIncrementOrderItem($orderId, $eventId);

        $freshRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($freshRow === null) {
            throw new \RuntimeException('Failed to load pending order.');
        }

        return $this->buildOrderFromRow($freshRow);
    }

    public function removeItemFromPendingOrder(int $userId, int $orderItemId): ?Order
    {
        if ($userId <= 0 || $orderItemId <= 0) {
            throw new \InvalidArgumentException('Invalid input for removing order item.');
        }

        $orderRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($orderRow === null) {
            return null;
        }

        $orderId = (int)$orderRow['order_id'];
        $this->orderRepository->removeOrderItem($orderId, $orderItemId);

        if ($this->orderRepository->countItems($orderId) === 0) {
            $this->orderRepository->deleteOrder($orderId);
            return null;
        }

        $freshRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($freshRow === null) {
            return null;
        }

        return $this->buildOrderFromRow($freshRow);
    }

    public function updateItemQuantityInPendingOrder(int $userId, int $orderItemId, int $quantity): ?Order
    {
        if ($userId <= 0 || $orderItemId <= 0 || $quantity <= 0) {
            throw new \InvalidArgumentException('Invalid input for updating order item quantity.');
        }

        $orderRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($orderRow === null) {
            return null;
        }

        $orderId = (int)$orderRow['order_id'];
        $updated = $this->orderRepository->updateOrderItemQuantity($orderId, $orderItemId, $quantity);
        if (!$updated) {
            throw new \RuntimeException('Order item not found in your cart.');
        }

        $freshRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($freshRow === null) {
            return null;
        }

        return $this->buildOrderFromRow($freshRow);
    }

    private function buildOrderFromRow(array $orderRow): Order
    {
        $orderId = (int)($orderRow['order_id'] ?? 0);
        $itemsRows = $this->orderRepository->getOrderItemsWithEventData($orderId);

        $items = [];
        foreach ($itemsRows as $row) {
            $event = $this->eventBuilder->buildEventModel($row);
            $items[] = new OrderItem(
                (int)($row['order_item_id'] ?? 0),
                (int)($row['order_id'] ?? 0),
                (int)($row['event_id'] ?? 0),
                (int)($row['quantity'] ?? 1),
                isset($row['order_item_created_at']) ? (string)$row['order_item_created_at'] : null,
                $event
            );
        }

        $statusRaw = strtolower((string)($orderRow['order_status'] ?? 'pending'));
        $status = $statusRaw === OrderStatus::PAYED->value
            ? OrderStatus::PAYED
            : OrderStatus::PENDING;

        return new Order(
            (int)($orderRow['order_id'] ?? 0),
            (int)($orderRow['user_id'] ?? 0),
            $status,
            isset($orderRow['created_at']) ? (string)$orderRow['created_at'] : null,
            $items
        );
    }
}
