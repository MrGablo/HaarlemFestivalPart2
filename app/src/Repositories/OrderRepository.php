<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IOrderRepository;
use App\Support\VenueSchemaHelper;
use PDO;

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

        return (int) $this->getConnection()->lastInsertId();
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
                    ':order_item_id' => (int) $row['order_item_id'],
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
        $pdo = $this->getConnection();
        $rows = [];

        $jazzSql = 'SELECT
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
                j.location AS location,
                j.artist_id,
                a.name AS artist_name,
                j.img_background,
                j.price,
                j.page_id,
                NULL AS venue_id
             FROM `order_items` oi
             INNER JOIN Event e ON e.event_id = oi.event_id AND e.event_type = \'jazz\'
             LEFT JOIN JazzEvent j ON j.event_id = e.event_id
             LEFT JOIN Artist a ON a.artist_id = j.artist_id
             WHERE oi.order_id = :order_id';

        try {
            $stmt = $pdo->prepare($jazzSql);
            $stmt->execute([':order_id' => $orderId]);
            $jazz = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($jazz as $r) {
                $rows[] = $r;
            }
        } catch (\Throwable) {
            // Jazz schema differs; skip jazz lines rather than failing whole cart
        }

        $vpk = VenueSchemaHelper::primaryKeyColumn($pdo);
        $vt = '`' . str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo)) . '`';
        $vName = VenueSchemaHelper::displayNameExpression($pdo, 'v');
        $danceSql = 'SELECT
                oi.order_item_id,
                oi.order_id,
                oi.event_id,
                oi.quantity,
                oi.created_at AS order_item_created_at,
                e.event_id AS event_id,
                e.title AS title,
                e.event_type AS event_type,
                NULL AS start_date,
                NULL AS end_date,
                ' . $vName . ' AS location,
                NULL AS artist_id,
                NULL AS artist_name,
                NULL AS img_background,
                d.price,
                NULL AS page_id,
                d.venue_id AS venue_id
             FROM `order_items` oi
             INNER JOIN Event e ON e.event_id = oi.event_id AND e.event_type = \'dance\'
             LEFT JOIN DanceEvent d ON d.event_id = e.event_id
             LEFT JOIN ' . $vt . ' v ON v.`' . $vpk . '` = d.venue_id
             WHERE oi.order_id = :order_id';

        $stmt = $pdo->prepare($danceSql);
        $stmt->execute([':order_id' => $orderId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $rows[] = $r;
        }

        $otherSql = 'SELECT
                oi.order_item_id,
                oi.order_id,
                oi.event_id,
                oi.quantity,
                oi.created_at AS order_item_created_at,
                e.event_id AS event_id,
                e.title AS title,
                e.event_type AS event_type,
                NULL AS start_date,
                NULL AS end_date,
                \'\' AS location,
                NULL AS artist_id,
                NULL AS artist_name,
                NULL AS img_background,
                NULL AS price,
                NULL AS page_id,
                NULL AS venue_id
             FROM `order_items` oi
             INNER JOIN Event e ON e.event_id = oi.event_id
             WHERE oi.order_id = :order_id
               AND LOWER(e.event_type) NOT IN (\'jazz\', \'dance\')';
        $stmt = $pdo->prepare($otherSql);
        $stmt->execute([':order_id' => $orderId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $rows[] = $r;
        }

        usort($rows, static function (array $a, array $b): int {
            $ta = (string) ($a['order_item_created_at'] ?? '');
            $tb = (string) ($b['order_item_created_at'] ?? '');
            $c = strcmp($tb, $ta);

            return $c !== 0 ? $c : ((int) ($b['order_item_id'] ?? 0) - (int) ($a['order_item_id'] ?? 0));
        });

        return $rows;
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

        return (bool) $existsStmt->fetchColumn();
    }

    public function countItems(int $orderId): int
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT COUNT(*) FROM `order_items` WHERE order_id = :order_id'
        );
        $stmt->execute([':order_id' => $orderId]);

        return (int) $stmt->fetchColumn();
    }

    public function deleteOrder(int $orderId): void
    {
        $stmt = $this->getConnection()->prepare('DELETE FROM `orders` WHERE order_id = :order_id');
        $stmt->execute([':order_id' => $orderId]);
    }

    public function findEventById(int $eventId): ?array
    {
        $pdo = $this->getConnection();
        $st = $pdo->prepare('SELECT event_type FROM Event WHERE event_id = :id LIMIT 1');
        $st->execute([':id' => $eventId]);
        $meta = $st->fetch(PDO::FETCH_ASSOC);
        if (!is_array($meta)) {
            return null;
        }
        $type = strtolower((string) ($meta['event_type'] ?? ''));

        if ($type === 'dance') {
            $vpk = VenueSchemaHelper::primaryKeyColumn($pdo);
            $vt = '`' . str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo)) . '`';
            $vName = VenueSchemaHelper::displayNameExpression($pdo, 'v');
            $sql = 'SELECT
                e.event_id,
                e.title,
                e.event_type,
                NULL AS start_date,
                NULL AS end_date,
                ' . $vName . ' AS location,
                NULL AS artist_id,
                NULL AS artist_name,
                NULL AS img_background,
                d.price,
                NULL AS page_id,
                d.venue_id AS venue_id
             FROM Event e
             INNER JOIN DanceEvent d ON d.event_id = e.event_id
             LEFT JOIN ' . $vt . ' v ON v.`' . $vpk . '` = d.venue_id
             WHERE e.event_id = :id AND e.event_type = \'dance\'
             LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $eventId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return is_array($row) ? $row : null;
        }

        if ($type === 'jazz') {
            $sql = 'SELECT
                e.event_id,
                e.title,
                e.event_type,
                j.start_date,
                j.end_date,
                j.location AS location,
                j.artist_id,
                a.name AS artist_name,
                j.img_background,
                j.price,
                j.page_id,
                NULL AS venue_id
             FROM Event e
             INNER JOIN JazzEvent j ON j.event_id = e.event_id
             LEFT JOIN Artist a ON a.artist_id = j.artist_id
             WHERE e.event_id = :id AND e.event_type = \'jazz\'
             LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $eventId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return is_array($row) ? $row : null;
        }

        $stmt = $pdo->prepare(
            'SELECT event_id, title, event_type,
                    NULL AS start_date, NULL AS end_date,
                    \'\' AS location,
                    NULL AS artist_id, NULL AS artist_name, NULL AS img_background,
                    NULL AS price, NULL AS page_id, NULL AS venue_id
               FROM Event WHERE event_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $eventId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }
}
