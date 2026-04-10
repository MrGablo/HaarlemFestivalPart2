<?php

namespace App\Models;

class Order
{
    public int $order_id;
    public int $user_id;
    public OrderStatus $order_status;
    public ?string $created_at;

    /** @var OrderItem[] */
    public array $items;

    /** @param OrderItem[] $items */
    public function __construct(
        int $orderId,
        int $userId,
        OrderStatus $status,
        ?string $createdAt = null,
        array $items = []
    ) {
        $this->order_id = $orderId;
        $this->user_id = $userId;
        $this->order_status = $status;
        $this->created_at = $createdAt;
        $this->items = $items;
    }

    public function getItemCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            $count += max(0, (int)$item->quantity);
        }

        return $count;
    }

    public function getTotalPrice(): float
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item->getTotalPrice();
        }

        return $total;
    }
}
