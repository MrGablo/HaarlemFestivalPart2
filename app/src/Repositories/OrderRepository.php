<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IOrderRepository;

class OrderRepository extends Repository implements IOrderRepository
{
    public function findPendingOrderByUserId(int $userId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT order_id, user_id, order_status, created_at
               FROM `orders`
             WHERE user_id = :user_id AND order_status = :status
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

    public function createPendingOrder(int $userId): int
    {
        $stmt = $this->getConnection()->prepare(
            'INSERT INTO `orders` (user_id, order_status, created_at)
             VALUES (:user_id, :status, NOW())'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':status' => 'pending',
        ]);

        return (int)$this->getConnection()->lastInsertId();
    }

    public function addOrIncrementOrderItem(int $orderId, int $eventId): void
    {
        $pdo = $this->getConnection();
        $pdo->beginTransaction();

        try {
            $existing = $pdo->prepare(
                'SELECT order_item_id, quantity
                  FROM `order_items`
                 WHERE order_id = :order_id AND event_id = :event_id
                 LIMIT 1'
            );
            $existing->execute([
                ':order_id' => $orderId,
                ':event_id' => $eventId,
            ]);

            $row = $existing->fetch();

            if (is_array($row)) {
                $update = $pdo->prepare(
                    'UPDATE `order_items`
                     SET quantity = quantity + 1
                     WHERE order_item_id = :order_item_id'
                );
                $update->execute([
                    ':order_item_id' => (int)$row['order_item_id'],
                ]);
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO `order_items` (order_id, event_id, quantity, created_at)
                     VALUES (:order_id, :event_id, 1, NOW())'
                );
                $insert->execute([
                    ':order_id' => $orderId,
                    ':event_id' => $eventId,
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function getOrderItemsWithEventData(int $orderId): array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT
                oi.order_item_id,
                oi.order_id,
                oi.event_id,
                oi.quantity,
                oi.created_at AS order_item_created_at,
                e.event_id AS event_id,
                e.title AS title,
                e.event_type AS event_type,
                j.start_date,
                j.end_date,
                 j.venue_id,
                 v.name AS venue_name,
                     j.artist_id,
                     a.name AS artist_name,
                j.img_background,
                j.price,
                j.page_id
             FROM `order_items` oi
             INNER JOIN Event e ON e.event_id = oi.event_id
             LEFT JOIN JazzEvent j ON j.event_id = e.event_id
                  LEFT JOIN Artist a ON a.artist_id = j.artist_id
                  LEFT JOIN Venue v ON v.venue_id = j.venue_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.created_at DESC, oi.order_item_id DESC'
        );

        $stmt->execute([':order_id' => $orderId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function removeOrderItem(int $orderId, int $orderItemId): bool
    {
        $stmt = $this->getConnection()->prepare(
            'DELETE FROM `order_items`
             WHERE order_id = :order_id AND order_item_id = :order_item_id'
        );

        $stmt->execute([
            ':order_id' => $orderId,
            ':order_item_id' => $orderItemId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function updateOrderItemQuantity(int $orderId, int $orderItemId, int $quantity): bool
    {
        $stmt = $this->getConnection()->prepare(
            'UPDATE `order_items`
             SET quantity = :quantity
             WHERE order_id = :order_id AND order_item_id = :order_item_id'
        );

        $stmt->execute([
            ':quantity' => $quantity,
            ':order_id' => $orderId,
            ':order_item_id' => $orderItemId,
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        $existsStmt = $this->getConnection()->prepare(
            'SELECT 1 FROM `order_items`
             WHERE order_id = :order_id AND order_item_id = :order_item_id
             LIMIT 1'
        );
        $existsStmt->execute([
            ':order_id' => $orderId,
            ':order_item_id' => $orderItemId,
        ]);

        return (bool)$existsStmt->fetchColumn();
    }

    public function countItems(int $orderId): int
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT COUNT(*) FROM `order_items` WHERE order_id = :order_id'
        );
        $stmt->execute([':order_id' => $orderId]);

        return (int)$stmt->fetchColumn();
    }

    public function deleteOrder(int $orderId): void
    {
        $stmt = $this->getConnection()->prepare('DELETE FROM `orders` WHERE order_id = :order_id');
        $stmt->execute([':order_id' => $orderId]);
    }

    public function findEventById(int $eventId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT
                e.event_id,
                e.title,
                e.event_type,
                j.start_date,
                j.end_date,
                 j.venue_id,
                 v.name AS venue_name,
                     j.artist_id,
                     a.name AS artist_name,
                j.img_background,
                j.price,
                j.page_id
             FROM Event e
             LEFT JOIN JazzEvent j ON j.event_id = e.event_id
                  LEFT JOIN Artist a ON a.artist_id = j.artist_id
                  LEFT JOIN Venue v ON v.venue_id = j.venue_id
             WHERE e.event_id = :event_id
             LIMIT 1'
        );

        $stmt->execute([':event_id' => $eventId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }
}
