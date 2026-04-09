<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Support\VenueSchemaHelper;

/**
 * Simple repository for Stripe payment flow.
 * Creates and updates orders used by checkout fulfilment.
 * Marks pending orders paid and creates tickets after successful checkout.
 */
class PaymentRepository extends Repository
{
    private ?string $orderItemPassDateColumn = null;

    public function findPendingOrderByUserId(int $userId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT order_id, user_id
               FROM `orders`
              WHERE user_id = :user_id
                AND order_status = :status
              ORDER BY created_at DESC
              LIMIT 1'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':status' => 'pending',
        ]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function getPendingOrderItemsWithPricing(int $orderId): array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT
                oi.order_item_id,
                oi.order_id,
                oi.event_id,
                oi.quantity,
                e.title,
                COALESCE(j.price, d.price, p.base_price, 0) AS price
             FROM `order_items` oi
             INNER JOIN Event e ON e.event_id = oi.event_id
             LEFT JOIN JazzEvent j ON j.event_id = e.event_id
             LEFT JOIN DanceEvent d ON d.event_id = e.event_id
             LEFT JOIN PassEvent p ON p.event_id = e.event_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.created_at ASC, oi.order_item_id ASC'
        );
        $stmt->execute([':order_id' => $orderId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    /**
     * Find an event and its price by ID.
     */
    public function findEventById(int $eventId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT e.event_id, e.title, e.event_type, COALESCE(j.price, d.price, p.base_price, 0) AS price
               FROM Event e
               LEFT JOIN JazzEvent j ON j.event_id = e.event_id
               LEFT JOIN DanceEvent d ON d.event_id = e.event_id
               LEFT JOIN PassEvent p ON p.event_id = e.event_id
              WHERE e.event_id = :event_id
              LIMIT 1'
        );

        $stmt->execute([':event_id' => $eventId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function findPendingOrderItemForUser(int $userId, int $orderItemId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT
                oi.order_item_id,
                oi.order_id,
                oi.event_id,
                oi.quantity,
                e.title,
                                COALESCE(j.price, d.price, p.base_price, 0) AS price
             FROM `order_items` oi
             INNER JOIN `orders` o ON o.order_id = oi.order_id
             INNER JOIN Event e ON e.event_id = oi.event_id
             LEFT JOIN JazzEvent j ON j.event_id = e.event_id
             LEFT JOIN DanceEvent d ON d.event_id = e.event_id
                         LEFT JOIN PassEvent p ON p.event_id = e.event_id
             WHERE o.user_id = :user_id
               AND o.order_status = :status
               AND oi.order_item_id = :order_item_id
             LIMIT 1'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':status' => 'pending',
            ':order_item_id' => $orderItemId,
        ]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * Update an existing order's status to 'paid'.
     */
    public function markOrderAsPaid(int $orderId, int $userId): void
    {
        $stmt = $this->getConnection()->prepare(
            'UPDATE `orders`
             SET order_status = :status
             WHERE order_id = :order_id
               AND user_id = :user_id'
        );
        $stmt->execute([
            ':status'   => 'payed',
            ':order_id' => $orderId,
            ':user_id'  => $userId,
        ]);
    }

    public function isOrderPaid(int $orderId): bool
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT order_status FROM `orders` WHERE order_id = :order_id LIMIT 1'
        );
        $stmt->execute([':order_id' => $orderId]);
        $status = $stmt->fetchColumn();

        return $status === 'payed';
    }

    /**
     * Create a new order with status 'payed'.
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
            ':status'  => 'payed',
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

    public function getOrderItemsByOrderId(int $orderId): array
    {
        $passDateExpr = $this->orderItemsPassDateSelectExpression('oi');

        $stmt = $this->getConnection()->prepare(
            "SELECT
                oi.order_item_id,
                oi.order_id,
                oi.event_id,
                oi.quantity,
                {$passDateExpr} AS pass_date
             FROM `order_items` oi
                 WHERE order_id = :order_id"
        );
        $stmt->execute([':order_id' => $orderId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function getOrderDeliveryRecipient(int $orderId, int $userId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT
                o.order_id,
                o.user_id,
                u.first_name,
                u.last_name,
                u.email
             FROM `orders` o
             INNER JOIN `User` u ON u.id = o.user_id
             WHERE o.order_id = :order_id
               AND o.user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute([
            ':order_id' => $orderId,
            ':user_id' => $userId,
        ]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function getIssuedTicketsForOrder(int $orderId): array
    {
        $pdo = $this->getConnection();
        $vt = '`' . str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo)) . '`';
        $vpk = '`' . str_replace('`', '``', VenueSchemaHelper::primaryKeyColumn($pdo)) . '`';
        $danceVenueName = VenueSchemaHelper::displayNameExpression($pdo, 'vd');

        $stmt = $pdo->prepare(
            "SELECT
                t.ticket_id,
                t.qr,
                COALESCE(t.event_id, oi.event_id) AS event_id,
                e.title,
                e.event_type,
                COALESCE(j.start_date, d.start_date, y.start_time, s.start_date) AS event_start_time,
                CASE
                    WHEN LOWER(TRIM(e.event_type)) = 'dance' THEN {$danceVenueName}
                    ELSE COALESCE(NULLIF(TRIM(v.name), ''), '')
                END AS venue_name,
                COALESCE(j.price, d.price, p.base_price, 0) AS price
             FROM `Ticket` t
             INNER JOIN `order_items` oi ON oi.order_item_id = t.order_item_id
             INNER JOIN `Event` e ON e.event_id = COALESCE(t.event_id, oi.event_id)
             LEFT JOIN `JazzEvent` j ON j.event_id = e.event_id
             LEFT JOIN `DanceEvent` d ON d.event_id = e.event_id
             LEFT JOIN `YummyEvent` y ON y.event_id = e.event_id
             LEFT JOIN `StoriesEvent` s ON s.event_id = e.event_id
             LEFT JOIN `PassEvent` p ON p.event_id = e.event_id
             LEFT JOIN {$vt} v ON v.{$vpk} = j.venue_id
             LEFT JOIN {$vt} vd ON vd.{$vpk} = d.venue_id
             WHERE oi.order_id = :order_id
             ORDER BY t.ticket_id ASC"
        );
        $stmt->execute([':order_id' => $orderId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function removePendingOrderItemForUser(int $userId, int $orderItemId): void
    {
        $pdo = $this->getConnection();

        $orderStmt = $pdo->prepare(
            'SELECT o.order_id
             FROM `orders` o
             INNER JOIN `order_items` oi ON oi.order_id = o.order_id
             WHERE o.user_id = :user_id
               AND o.order_status = :status
               AND oi.order_item_id = :order_item_id
             LIMIT 1'
        );
        $orderStmt->execute([
            ':user_id' => $userId,
            ':status' => 'pending',
            ':order_item_id' => $orderItemId,
        ]);
        $orderId = (int)$orderStmt->fetchColumn();
        if ($orderId <= 0) {
            return;
        }

        $deleteItemStmt = $pdo->prepare(
            'DELETE oi
             FROM `order_items` oi
             INNER JOIN `orders` o ON o.order_id = oi.order_id
             WHERE oi.order_item_id = :order_item_id
               AND o.user_id = :user_id
               AND o.order_status = :status'
        );
        $deleteItemStmt->execute([
            ':order_item_id' => $orderItemId,
            ':user_id' => $userId,
            ':status' => 'pending',
        ]);

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM `order_items` WHERE order_id = :order_id');
        $countStmt->execute([':order_id' => $orderId]);
        $itemCount = (int)$countStmt->fetchColumn();

        if ($itemCount <= 0) {
            $deleteOrderStmt = $pdo->prepare(
                'DELETE FROM `orders`
                 WHERE order_id = :order_id
                   AND user_id = :user_id
                   AND order_status = :status'
            );
            $deleteOrderStmt->execute([
                ':order_id' => $orderId,
                ':user_id' => $userId,
                ':status' => 'pending',
            ]);
        }
    }

    public function removePendingOrderForUser(int $userId, int $orderId): void
    {
        $pdo = $this->getConnection();

        $pdo->beginTransaction();
        try {
            $deleteItems = $pdo->prepare(
                'DELETE oi
                 FROM `order_items` oi
                 INNER JOIN `orders` o ON o.order_id = oi.order_id
                 WHERE o.order_id = :order_id
                   AND o.user_id = :user_id
                   AND o.order_status = :status'
            );
            $deleteItems->execute([
                ':order_id' => $orderId,
                ':user_id' => $userId,
                ':status' => 'pending',
            ]);

            $deleteOrder = $pdo->prepare(
                'DELETE FROM `orders`
                 WHERE order_id = :order_id
                   AND user_id = :user_id
                   AND order_status = :status'
            );
            $deleteOrder->execute([
                ':order_id' => $orderId,
                ':user_id' => $userId,
                ':status' => 'pending',
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function orderItemsPassDateColumn(): ?string
    {
        if ($this->orderItemPassDateColumn !== null) {
            return $this->orderItemPassDateColumn;
        }

        $stmt = $this->getConnection()->query("SHOW COLUMNS FROM `order_items` LIKE 'pass_date_key'");
        $row = $stmt ? $stmt->fetch() : false;
        if (is_array($row)) {
            $this->orderItemPassDateColumn = 'pass_date_key';
            return $this->orderItemPassDateColumn;
        }

        $stmt = $this->getConnection()->query("SHOW COLUMNS FROM `order_items` LIKE 'pass_date'");
        $row = $stmt ? $stmt->fetch() : false;
        if (is_array($row)) {
            $this->orderItemPassDateColumn = 'pass_date';
            return $this->orderItemPassDateColumn;
        }

        return null;
    }

    private function orderItemsPassDateSelectExpression(string $tableAlias): string
    {
        $column = $this->orderItemsPassDateColumn();
        if ($column === null) {
            return 'NULL';
        }

        return $tableAlias . '.' . $column;
    }
}
