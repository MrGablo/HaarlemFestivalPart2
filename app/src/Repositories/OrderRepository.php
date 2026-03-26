<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IOrderRepository;
use App\Support\VenueSchemaHelper;

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

    public function getAllOrdersWithSummary(): array
    {
        $stmt = $this->getConnection()->query(
            'SELECT
                o.order_id,
                o.user_id,
                o.order_status,
                o.created_at,
                u.first_name,
                u.last_name,
                u.email,
                COALESCE(SUM(oi.quantity), 0) AS item_count,
                COALESCE(SUM(oi.quantity * COALESCE(j.price, d.price, 0)), 0) AS total_amount
             FROM `orders` o
             LEFT JOIN `User` u ON u.id = o.user_id
             LEFT JOIN `order_items` oi ON oi.order_id = o.order_id
             LEFT JOIN `JazzEvent` j ON j.event_id = oi.event_id
             LEFT JOIN `DanceEvent` d ON d.event_id = oi.event_id
             GROUP BY
                o.order_id,
                o.user_id,
                o.order_status,
                o.created_at,
                u.first_name,
                u.last_name,
                u.email'
        );

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findOrderSummaryById(int $orderId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT
                o.order_id,
                o.user_id,
                o.order_status,
                o.created_at,
                u.first_name,
                u.last_name,
                u.email,
                COALESCE(SUM(oi.quantity), 0) AS item_count,
                COALESCE(SUM(oi.quantity * COALESCE(j.price, d.price, 0)), 0) AS total_amount
             FROM `orders` o
             LEFT JOIN `User` u ON u.id = o.user_id
             LEFT JOIN `order_items` oi ON oi.order_id = o.order_id
             LEFT JOIN `JazzEvent` j ON j.event_id = oi.event_id
             LEFT JOIN `DanceEvent` d ON d.event_id = oi.event_id
             WHERE o.order_id = :order_id
             GROUP BY
                o.order_id,
                o.user_id,
                o.order_status,
                o.created_at,
                u.first_name,
                u.last_name,
                u.email
             LIMIT 1'
        );

        $stmt->execute([':order_id' => $orderId]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        $stmt = $this->getConnection()->prepare(
            'UPDATE `orders`
             SET order_status = :status
             WHERE order_id = :order_id'
        );

        $stmt->execute([
            ':status' => $status,
            ':order_id' => $orderId,
        ]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        $existsStmt = $this->getConnection()->prepare(
            'SELECT 1 FROM `orders`
             WHERE order_id = :order_id
             LIMIT 1'
        );
        $existsStmt->execute([':order_id' => $orderId]);

        return (bool)$existsStmt->fetchColumn();
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

    public function getOrderItemsForOrders(array $orderIds): array
    {
        if ($orderIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $pdo = $this->getConnection();
        $vt = '`' . str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo)) . '`';
        $vpk = '`' . str_replace('`', '``', VenueSchemaHelper::primaryKeyColumn($pdo)) . '`';
        $danceVenueName = VenueSchemaHelper::displayNameExpression($pdo, 'vd');

        $sql = "SELECT
                oi.order_item_id,
                oi.order_id,
                oi.event_id,
                oi.quantity,
                oi.created_at AS order_item_created_at,
                e.event_id,
                e.title AS title,
                e.event_type AS event_type,
                j.start_date,
                j.end_date,
                COALESCE(j.venue_id, d.venue_id) AS venue_id,
                v.name AS venue_name,
                j.artist_id,
                a.name AS artist_name,
                j.img_background,
                COALESCE(j.price, d.price, 0) AS price,
                j.page_id,
                CASE WHEN LOWER(TRIM(e.event_type)) = 'dance' THEN {$danceVenueName} ELSE '' END AS location
             FROM `order_items` oi
             INNER JOIN Event e ON e.event_id = oi.event_id
             LEFT JOIN JazzEvent j ON j.event_id = e.event_id
             LEFT JOIN DanceEvent d ON d.event_id = e.event_id
             LEFT JOIN Artist a ON a.artist_id = j.artist_id
             LEFT JOIN {$vt} v ON v.{$vpk} = j.venue_id
             LEFT JOIN {$vt} vd ON vd.{$vpk} = d.venue_id
             WHERE oi.order_id IN ($placeholders)
             ORDER BY oi.order_id ASC, oi.created_at DESC, oi.order_item_id DESC";

        $stmt = $pdo->prepare($sql);

        $stmt->execute(array_values($orderIds));
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function getOrderItemsWithEventData(int $orderId): array
    {
        $pdo = $this->getConnection();
        $vt = '`' . str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo)) . '`';
        $vpk = '`' . str_replace('`', '``', VenueSchemaHelper::primaryKeyColumn($pdo)) . '`';
        $danceVenueName = VenueSchemaHelper::displayNameExpression($pdo, 'vd');

        $sql = "SELECT
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
                COALESCE(j.venue_id, d.venue_id) AS venue_id,
                v.name AS venue_name,
                j.artist_id,
                a.name AS artist_name,
                j.img_background,
                COALESCE(j.price, d.price, 0) AS price,
                j.page_id,
                CASE WHEN LOWER(TRIM(e.event_type)) = 'dance' THEN {$danceVenueName} ELSE '' END AS location
             FROM `order_items` oi
             INNER JOIN Event e ON e.event_id = oi.event_id
             LEFT JOIN JazzEvent j ON j.event_id = e.event_id
             LEFT JOIN DanceEvent d ON d.event_id = e.event_id
             LEFT JOIN Artist a ON a.artist_id = j.artist_id
             LEFT JOIN {$vt} v ON v.{$vpk} = j.venue_id
             LEFT JOIN {$vt} vd ON vd.{$vpk} = d.venue_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.created_at DESC, oi.order_item_id DESC";

        $stmt = $pdo->prepare($sql);

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
        $pdo = $this->getConnection();
        $vt = '`' . str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo)) . '`';
        $vpk = '`' . str_replace('`', '``', VenueSchemaHelper::primaryKeyColumn($pdo)) . '`';
        $danceVenueName = VenueSchemaHelper::displayNameExpression($pdo, 'vd');

        $sql = "SELECT
                e.event_id,
                e.title,
                e.event_type,
                j.start_date,
                j.end_date,
                COALESCE(j.venue_id, d.venue_id) AS venue_id,
                v.name AS venue_name,
                j.artist_id,
                a.name AS artist_name,
                j.img_background,
                COALESCE(j.price, d.price, 0) AS price,
                j.page_id,
                CASE WHEN LOWER(TRIM(e.event_type)) = 'dance' THEN {$danceVenueName} ELSE '' END AS location
             FROM Event e
             LEFT JOIN JazzEvent j ON j.event_id = e.event_id
             LEFT JOIN DanceEvent d ON d.event_id = e.event_id
             LEFT JOIN Artist a ON a.artist_id = j.artist_id
             LEFT JOIN {$vt} v ON v.{$vpk} = j.venue_id
             LEFT JOIN {$vt} vd ON vd.{$vpk} = d.venue_id
             WHERE e.event_id = :event_id
             LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([':event_id' => $eventId]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * Return all orders (any status) for a given user, newest first.
     */
    public function findOrdersByUserId(int $userId): array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT order_id, user_id, order_status, created_at
               FROM `orders`
              WHERE user_id = :user_id
              ORDER BY created_at DESC, order_id DESC'
        );

        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }
}