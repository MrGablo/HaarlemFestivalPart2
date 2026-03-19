<?php

namespace App\Repositories;

use App\Framework\Repository;

/**
 * Simple repository for Stripe payment flow.
 * Creates paid orders, order items, and tickets after successful checkout.
 */
class PaymentRepository extends Repository
{
    /**
     * Find an event and its price by ID.
     */
    public function findEventById(int $eventId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT e.event_id, e.title, e.event_type, j.price
               FROM Event e
               LEFT JOIN JazzEvent j ON j.event_id = e.event_id
              WHERE e.event_id = :event_id
              LIMIT 1'
        );

        $stmt->execute([':event_id' => $eventId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * Create a new order with status 'paid'.
     * Returns the new order ID.
     */
    public function createPaidOrder(int $userId): int
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO `orders` (user_id, order_status, created_at)
             VALUES (:user_id, :status, NOW())'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':status'  => 'paid',
        ]);

        return (int) $this->getConnection()->lastInsertId();
    }

    /**
     * Create an order item linked to the order and event.
     * Returns the new order_item ID.
     */
    public function createOrderItem(int $orderId, int $eventId, int $quantity): int
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO `order_items` (order_id, event_id, quantity, created_at)
             VALUES (:order_id, :event_id, :quantity, NOW())'
        );

        $stmt->execute([
            ':order_id' => $orderId,
            ':event_id' => $eventId,
            ':quantity' => $quantity,
        ]);

        return (int) $this->getConnection()->lastInsertId();
    }

    /**
     * Create a ticket linked to the order item and user.
     * Returns the new ticket ID.
     */
    public function createTicket(int $orderItemId, int $userId, string $qr): int
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO `tickets` (order_item_id, user_id, qr)
             VALUES (:order_item_id, :user_id, :qr)'
        );

        $stmt->execute([
            ':order_item_id' => $orderItemId,
            ':user_id'       => $userId,
            ':qr'            => $qr,
        ]);

        return (int) $this->getConnection()->lastInsertId();
    }
}
