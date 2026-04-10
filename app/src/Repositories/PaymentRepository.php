<?php

namespace App\Repositories;

use App\Framework\Repository;
use App\Repositories\Interfaces\IPaymentRepository;
use App\Support\VenueSchemaHelper;

/**
 * Data access for Stripe checkout fulfilment: pending order lookup,
 * mark paid, order items for ticketing, email/PDF payload.
 */
class PaymentRepository extends Repository implements IPaymentRepository
{
    private const ORDER_STATUS_PENDING = 'pending';
    private const ORDER_STATUS_PAID = 'payed';

    private ?string $orderItemPassDateColumn = null;

    public function findPendingOrderByUserId(int $userId): ?array
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT order_id, user_id
               FROM `orders`
              WHERE user_id = :user_id
                AND order_status = :status
                AND (
                     payment_deadline_at IS NULL
                     OR payment_deadline_at > NOW()
                    )
              ORDER BY created_at DESC
              LIMIT 1'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':status' => self::ORDER_STATUS_PENDING,
        ]);
        $row = $stmt->fetch();

        return is_array($row) ? $row : null;
    }

    public function markOrderAsPaid(int $orderId, int $userId): bool
    {
        $stmt = $this->getConnection()->prepare(
            'UPDATE `orders`
             SET order_status = :status,
                 payment_deadline_at = NULL
             WHERE order_id = :order_id
               AND user_id = :user_id
               AND order_status = :pending'
        );
        $stmt->execute([
            ':status'   => self::ORDER_STATUS_PAID,
            ':pending'  => self::ORDER_STATUS_PENDING,
            ':order_id' => $orderId,
            ':user_id'  => $userId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function isOrderPaid(int $orderId): bool
    {
        $stmt = $this->getConnection()->prepare(
            'SELECT order_status FROM `orders` WHERE order_id = :order_id LIMIT 1'
        );
        $stmt->execute([':order_id' => $orderId]);
        $status = $stmt->fetchColumn();

        return $status === self::ORDER_STATUS_PAID;
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
                o.created_at,
                o.order_status,
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

    public function getInvoiceLineItems(int $orderId): array
    {
        $pdo = $this->getConnection();
        $vt = '`' . str_replace('`', '``', VenueSchemaHelper::venueTableName($pdo)) . '`';
        $vpk = '`' . str_replace('`', '``', VenueSchemaHelper::primaryKeyColumn($pdo)) . '`';
        $danceVenueName = VenueSchemaHelper::displayNameExpression($pdo, 'vd');
        $passDateExpr = $this->orderItemsPassDateSelectExpression('oi');

        $stmt = $pdo->prepare(
            "SELECT
                oi.order_item_id,
                oi.quantity,
                {$passDateExpr} AS pass_date,
                e.title,
                e.event_type,
                COALESCE(j.start_date, d.start_date, h.start_date, y.start_time, s.start_date) AS event_start_time,
                CASE
                    WHEN LOWER(TRIM(e.event_type)) = 'dance' THEN {$danceVenueName}
                    WHEN LOWER(TRIM(e.event_type)) = 'history' THEN COALESCE(h.location, '')
                    ELSE COALESCE(NULLIF(TRIM(v.name), ''), '')
                END AS venue_name,
                COALESCE(j.price, d.price, h.price, y.price, s.price, p.base_price, 0) AS unit_price,
                h.family_price
             FROM `order_items` oi
             INNER JOIN `Event` e ON e.event_id = oi.event_id
             LEFT JOIN `JazzEvent` j ON j.event_id = e.event_id
             LEFT JOIN `DanceEvent` d ON d.event_id = e.event_id
             LEFT JOIN `HistoryEvent` h ON h.event_id = e.event_id
             LEFT JOIN `YummyEvent` y ON y.event_id = e.event_id
             LEFT JOIN `StoriesEvent` s ON s.event_id = e.event_id
             LEFT JOIN `PassEvent` p ON p.event_id = e.event_id
             LEFT JOIN {$vt} v ON v.{$vpk} = j.venue_id
             LEFT JOIN {$vt} vd ON vd.{$vpk} = d.venue_id
             WHERE oi.order_id = :order_id
             ORDER BY oi.order_item_id ASC"
        );
        $stmt->execute([':order_id' => $orderId]);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
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
                COALESCE(j.price, d.price, y.price, s.price, p.base_price, 0) AS price
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
