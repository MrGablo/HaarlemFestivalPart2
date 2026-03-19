<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\PassEvent;
use App\Repositories\PassRepository;
use App\Repositories\Interfaces\IOrderRepository;

class OrderService
{
    public function __construct(
        private IOrderRepository $orderRepository,
        private EventModelBuilderService $eventBuilder,
        private ?PassService $passService = null
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

    public function addEventToUserPendingOrder(int $userId, int $eventId, ?string $passDate = null): Order
    {
        $this->assertPositiveId($userId, 'user id');
        $this->assertPositiveId($eventId, 'event id');

        $eventRow = $this->orderRepository->findEventById($eventId);
        if ($eventRow === null) {
            throw new \RuntimeException('Event not found.');
        }

        $eventRowId = (int)($eventRow['event_id'] ?? 0);
        if ($eventRowId <= 0 || $eventRowId !== $eventId) {
            throw new \RuntimeException('Invalid event data returned from repository.');
        }

        // Validate event type can be mapped before storing the item.
        $this->eventBuilder->buildEventModel($eventRow);

        $passDate = $this->normalizePassDate($passDate);
        $effectivePassDate = null;

        $passService = $this->passService ?? new PassService(new PassRepository());
        $pass = $passService->findActivePassProductByEventId($eventId);

        if ($pass instanceof PassEvent) {
            $passScope = strtolower($pass->pass_scope);
            $festivalType = strtolower($pass->festival_type);

            if ($festivalType === 'jazz' && $passScope === 'day') {
                if ($passDate === null) {
                    throw new \RuntimeException('Please select a valid Jazz day for this pass.');
                }

                if (!$passService->isValidJazzPassDate($passDate)) {
                    throw new \RuntimeException('Selected Jazz day is unavailable for this pass.');
                }

                $effectivePassDate = $passDate;
            }
        } elseif ($passDate !== null) {
            throw new \RuntimeException('Pass date can only be used for pass products.');
        }

        $orderRow = $this->orderRepository->findPendingOrderByUserId($userId);
        $existingOrderId = $orderRow !== null
            ? $this->extractOrderId($orderRow, $userId)
            : 0;

        $createdOrderId = 0;
        if ($existingOrderId <= 0) {
            $createdOrderId = $this->orderRepository->createPendingOrder($userId);
            if ($createdOrderId <= 0) {
                throw new \RuntimeException('Failed to create pending order.');
            }
        }

        $orderId = $orderRow !== null
            ? $existingOrderId
            : $createdOrderId;

        $this->orderRepository->addOrIncrementOrderItem($orderId, $eventId, $effectivePassDate);

        $freshRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($freshRow === null) {
            throw new \RuntimeException('Failed to load pending order.');
        }

        $freshOrderId = $this->extractOrderId($freshRow, $userId);
        if ($freshOrderId !== $orderId) {
            throw new \RuntimeException('Loaded pending order does not match the updated cart.');
        }

        return $this->buildOrderFromRow($freshRow);
    }

    public function removeItemFromPendingOrder(int $userId, int $orderItemId): ?Order
    {
        $this->assertPositiveId($userId, 'user id');
        $this->assertPositiveId($orderItemId, 'order item id');

        $orderRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($orderRow === null) {
            return null;
        }

        $orderId = $this->extractOrderId($orderRow, $userId);
        $removed = $this->orderRepository->removeOrderItem($orderId, $orderItemId);
        if (!$removed) {
            throw new \RuntimeException('Order item not found in your cart.');
        }

        $itemCount = $this->orderRepository->countItems($orderId);
        if ($itemCount <= 0) {
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
        $this->assertPositiveId($userId, 'user id');
        $this->assertPositiveId($orderItemId, 'order item id');
        $this->assertPositiveId($quantity, 'quantity');

        $orderRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($orderRow === null) {
            return null;
        }

        $orderId = $this->extractOrderId($orderRow, $userId);
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
        $userId = (int)($orderRow['user_id'] ?? 0);
        if ($orderId <= 0 || $userId <= 0) {
            throw new \RuntimeException('Invalid order data returned from repository.');
        }

        $itemsRows = $this->orderRepository->getOrderItemsWithEventData($orderId);

        $items = [];
        foreach ($itemsRows as $row) {
            $rowOrderId = (int)($row['order_id'] ?? 0);
            $orderItemId = (int)($row['order_item_id'] ?? 0);
            $eventId = (int)($row['event_id'] ?? 0);
            $quantity = (int)($row['quantity'] ?? 0);

            if ($rowOrderId !== $orderId || $orderItemId <= 0 || $eventId <= 0 || $quantity <= 0) {
                throw new \RuntimeException('Invalid order item data returned from repository.');
            }

            $event = $this->eventBuilder->buildEventModel($row);
            $items[] = new OrderItem(
                $orderItemId,
                $rowOrderId,
                $eventId,
                $quantity,
                isset($row['pass_date']) ? (string)$row['pass_date'] : null,
                isset($row['order_item_created_at']) ? (string)$row['order_item_created_at'] : null,
                $event
            );
        }

        $statusRaw = strtolower((string)($orderRow['order_status'] ?? 'pending'));
        $status = $statusRaw === OrderStatus::PAYED->value
            ? OrderStatus::PAYED
            : OrderStatus::PENDING;

        return new Order(
            $orderId,
            $userId,
            $status,
            isset($orderRow['created_at']) ? (string)$orderRow['created_at'] : null,
            $items
        );
    }

    private function assertPositiveId(int $value, string $label): void
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Invalid ' . $label . '.');
        }
    }

    private function extractOrderId(array $orderRow, int $expectedUserId): int
    {
        $orderId = (int)($orderRow['order_id'] ?? 0);
        $rowUserId = (int)($orderRow['user_id'] ?? 0);

        if ($orderId <= 0 || $rowUserId <= 0 || $rowUserId !== $expectedUserId) {
            throw new \RuntimeException('Invalid pending order returned for user.');
        }

        return $orderId;
    }

    private function normalizePassDate(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;
        if ($value === null || $value === '') {
            return null;
        }

        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if (!$dt instanceof \DateTimeImmutable || $dt->format('Y-m-d') !== $value) {
            return null;
        }

        return $value;
    }
}
