<?php

namespace App\Models;

class OrderItem
{
    public int $order_item_id;
    public int $order_id;
    public int $event_id;
    public int $quantity;
    public ?string $created_at;
    public ?Event $event;

    public function __construct(
        int $orderItemId,
        int $orderId,
        int $eventId,
        int $quantity,
        ?string $createdAt = null,
        ?Event $event = null
    ) {
        $this->order_item_id = $orderItemId;
        $this->order_id = $orderId;
        $this->event_id = $eventId;
        $this->quantity = $quantity;
        $this->created_at = $createdAt;
        $this->event = $event;
    }

    public function getUnitPrice(): float
    {
        if ($this->event instanceof JazzEvent) {
            return $this->event->price;
        }

        return 0.0;
    }

    public function getLocation(): string
    {
        if ($this->event instanceof JazzEvent) {
            return $this->event->location;
        }

        return '';
    }
}
