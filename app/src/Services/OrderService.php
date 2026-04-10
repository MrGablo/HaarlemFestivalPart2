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
        private ?PassService $passService = null,
        private ?HistoryBookingPricingService $historyPricing = null
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

    /**
     * Cart or checkout-in-progress order (used for checkout URL, header cart count).
     */
    public function getPayablePendingOrderForUser(int $userId): ?Order
    {
        if ($userId <= 0) {
            return null;
        }

        $orderRow = $this->orderRepository->findPayablePendingOrderByUserId($userId);
        if ($orderRow === null) {
            return null;
        }

        return $this->buildOrderFromRow($orderRow);
    }

    /**
     * User clicked Pay: order is held until payment_deadline_at.
     */
    public function getAwaitingPaymentOrderForUser(int $userId): ?Order
    {
        if ($userId <= 0) {
            return null;
        }

        $orderRow = $this->orderRepository->findAwaitingPaymentOrderByUserId($userId);
        if ($orderRow === null) {
            return null;
        }

        return $this->buildOrderFromRow($orderRow);
    }

    public function expireStaleCheckoutDeadlines(): void
    {
        $this->orderRepository->cancelExpiredPendingPaymentOrders();
    }

    public function cancelAwaitingPaymentOrderForUser(int $userId, int $orderId): void
    {
        $this->assertPositiveId($userId, 'user id');
        $this->assertPositiveId($orderId, 'order id');

        if (!$this->orderRepository->cancelAwaitingPaymentOrderForUser($userId, $orderId)) {
            throw new \RuntimeException(
                'Unable to cancel this checkout. It may already be paid, cancelled, or not waiting for payment.'
            );
        }
    }

    public function markCheckoutStartedIfNeeded(int $userId, int $orderId): void
    {
        if ($userId <= 0 || $orderId <= 0) {
            return;
        }

        $this->orderRepository->ensurePaymentDeadlineFromFirstCheckout($orderId, $userId);
    }

    /**
     * @return Order[]
     */
    public function getCancelledOrdersWithItemsForUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $rows = $this->orderRepository->findCancelledOrdersByUserId($userId);
        $orders = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $orders[] = $this->buildOrderFromRow($row);
        }

        return $orders;
    }

    public function addEventToUserPendingOrder(int $userId, int $eventId, int $quantity = 1, ?string $passDate = null): Order
    {
        $this->assertPositiveId($userId, 'user id');
        $this->assertPositiveId($eventId, 'event id');
        $this->assertPositiveId($quantity, 'quantity');

        $eventRow = $this->orderRepository->findEventById($eventId);
        if ($eventRow === null) {
            throw new \RuntimeException('Event not found.');
        }

        $eventRowId = (int)($eventRow['event_id'] ?? 0);
        if ($eventRowId <= 0 || $eventRowId !== $eventId) {
            throw new \RuntimeException('Invalid event data returned from repository.');
        }

        // Validate event type can be mapped before storing the item.
        $eventModel = $this->eventBuilder->buildEventModel($eventRow);

        if (strtolower((string)($eventModel->event_type ?? '')) === 'history') {
            $maxHistoryTickets = $this->historyPricing()->maxTicketsPerOrder();
            if ($quantity > $maxHistoryTickets) {
                throw new \RuntimeException('A history booking can contain at most ' . $maxHistoryTickets . ' tickets.');
            }
        }

        $passDate = $this->normalizePassDate($passDate);
        $effectivePassDate = null;

        if ($this->orderRepository->findAwaitingPaymentOrderByUserId($userId) !== null) {
            throw new \RuntimeException(
                'You already have a checkout in progress. Open My Program to pay within 24 hours, or use Cancel checkout there to stop the order and add items again. Otherwise wait until it expires.'
            );
        }

        $passService = $this->passService ?? new PassService(new PassRepository());
        $pass = $passService->findActivePassProductByEventId($eventId);

        if ($pass instanceof PassEvent) {
            $passScope = strtolower($pass->pass_scope);
            $festivalType = strtolower($pass->festival_type);

            if ($passScope === 'day') {
                if ($passDate === null) {
                    throw new \RuntimeException('Please select a valid day for this pass.');
                }

                $isValidDay = match ($festivalType) {
                    'jazz' => $passService->isValidJazzPassDate($passDate),
                    'dance' => $passService->isValidDancePassDate($passDate),
                    default => false,
                };

                if (!$isValidDay) {
                    throw new \RuntimeException('Selected day is unavailable for this pass.');
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

        $currentQuantity = $this->findPendingItemQuantity($orderId, $eventId, $effectivePassDate);
        $nextQuantity = $currentQuantity + $quantity;

        $this->assertEventCapacityForQuantity($eventRow, $nextQuantity);

        if (strtolower((string)($eventModel->event_type ?? '')) === 'history') {
            $maxHistoryTickets = $this->historyPricing()->maxTicketsPerOrder();
            if ($nextQuantity > $maxHistoryTickets) {
                throw new \RuntimeException('A history booking can contain at most ' . $maxHistoryTickets . ' tickets.');
            }
        }

        $this->orderRepository->addOrIncrementOrderItem($orderId, $eventId, $quantity, $effectivePassDate);

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
        $this->assertNoAwaitingPaymentOrder($userId);

        $orderRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($orderRow === null) {
            throw new \RuntimeException('No editable cart found. Your checkout may already be in progress.');
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
        $this->assertNoAwaitingPaymentOrder($userId);

        $orderRow = $this->orderRepository->findPendingOrderByUserId($userId);
        if ($orderRow === null) {
            throw new \RuntimeException('No editable cart found. Your checkout may already be in progress.');
        }

        $currentOrder = $this->buildOrderFromRow($orderRow);
        foreach ($currentOrder->items as $item) {
            if ((int)$item->order_item_id !== $orderItemId) {
                continue;
            }

            if (strtolower((string)($item->event?->event_type ?? '')) === 'history') {
                $maxHistoryTickets = $this->historyPricing()->maxTicketsPerOrder();
                if ($quantity > $maxHistoryTickets) {
                    throw new \RuntimeException('A history booking can contain at most ' . $maxHistoryTickets . ' tickets.');
                }
            }

            break;
        }

        $orderId = $this->extractOrderId($orderRow, $userId);

        $itemsRows = $this->orderRepository->getOrderItemsWithEventData($orderId);
        $targetEventId = 0;
        foreach ($itemsRows as $row) {
            if ((int)($row['order_item_id'] ?? 0) === $orderItemId) {
                $targetEventId = (int)($row['event_id'] ?? 0);
                break;
            }
        }

        if ($targetEventId <= 0) {
            throw new \RuntimeException('Order item not found in your cart.');
        }

        $eventRow = $this->orderRepository->findEventById($targetEventId);
        if ($eventRow === null) {
            throw new \RuntimeException('Event not found.');
        }

        $this->assertEventCapacityForQuantity($eventRow, $quantity);

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
            $quantity = (int)($row['quantity'] ?? 0);
            $pricing = $this->resolveItemPricing($event, $quantity);

            $items[] = new OrderItem(
                $orderItemId,
                $rowOrderId,
                $eventId,
                $quantity,
                isset($row['pass_date']) ? (string)$row['pass_date'] : null,
                isset($row['order_item_created_at']) ? (string)$row['order_item_created_at'] : null,
                $event,
                $pricing['unit_price_override'],
                $pricing['line_total_override']
            );
        }

        $statusRaw = strtolower((string)($orderRow['order_status'] ?? 'pending'));
        $status = match ($statusRaw) {
            OrderStatus::PAYED->value => OrderStatus::PAYED,
            OrderStatus::CANCELLED->value => OrderStatus::CANCELLED,
            default => OrderStatus::PENDING,
        };

        $deadlineRaw = $orderRow['payment_deadline_at'] ?? null;
        $deadline = $deadlineRaw !== null && $deadlineRaw !== ''
            ? (string)$deadlineRaw
            : null;

        return new Order(
            $orderId,
            $userId,
            $status,
            isset($orderRow['created_at']) ? (string)$orderRow['created_at'] : null,
            $items,
            $deadline
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

    private function findPendingItemQuantity(int $orderId, int $eventId, ?string $passDate): int
    {
        if ($orderId <= 0 || $eventId <= 0) {
            return 0;
        }

        $itemsRows = $this->orderRepository->getOrderItemsWithEventData($orderId);
        $expectedPassDate = $this->normalizeComparablePassDate($passDate);

        foreach ($itemsRows as $row) {
            $rowEventId = (int)($row['event_id'] ?? 0);
            if ($rowEventId !== $eventId) {
                continue;
            }

            $rowPassDate = $this->normalizeComparablePassDate(isset($row['pass_date']) ? (string)$row['pass_date'] : null);
            if ($rowPassDate !== $expectedPassDate) {
                continue;
            }

            return (int)($row['quantity'] ?? 0);
        }

        return 0;
    }

    private function normalizeComparablePassDate(?string $value): ?string
    {
        $value = $this->normalizePassDate($value);
        if ($value === '1000-01-01') {
            return null;
        }

        return $value;
    }

    private function assertEventCapacityForQuantity(array $eventRow, int $requestedQuantity): void
    {
        if ($requestedQuantity <= 0) {
            throw new \InvalidArgumentException('Invalid quantity.');
        }

        $eventType = strtolower(trim((string)($eventRow['event_type'] ?? '')));
        if ($eventType === 'pass') {
            return;
        }

        $availability = (int)($eventRow['availability'] ?? 0);
        if ($requestedQuantity > $availability) {
            throw new \RuntimeException('Not enough seats available for this event.');
        }
    }

    private function assertNoAwaitingPaymentOrder(int $userId): void
    {
        if ($this->orderRepository->findAwaitingPaymentOrderByUserId($userId) !== null) {
            throw new \RuntimeException(
                'Checkout is in progress for this order. Open My Program to finish payment or cancel checkout first.'
            );
        }
    }

    /** @return array{unit_price_override: ?float, line_total_override: ?float} */
    private function resolveItemPricing(\App\Models\Event $event, int $quantity): array
    {
        if (strtolower((string)($event->event_type ?? '')) !== 'history') {
            return [
                'unit_price_override' => null,
                'line_total_override' => null,
            ];
        }

        $basePrice = $event instanceof \App\Models\GenericEvent ? (float)($event->price ?? 0.0) : 0.0;
        $familyPrice = $event instanceof \App\Models\HistoryEvent ? $event->family_price : null;
        $pricing = $this->historyPricing()->resolvePricing($basePrice, $familyPrice, $quantity);

        return [
            'unit_price_override' => $pricing['unit_price'],
            'line_total_override' => $pricing['total_price'],
        ];
    }

    private function historyPricing(): HistoryBookingPricingService
    {
        return $this->historyPricing ??= new HistoryBookingPricingService();
    }
}
