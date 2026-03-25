<?php

namespace App\Models;

class OrderItem
{
    public int $order_item_id;
    public int $order_id;
    public int $event_id;
    public int $quantity;
    public ?string $pass_date;
    public ?string $created_at;
    public ?Event $event;

    public function __construct(
        int $orderItemId,
        int $orderId,
        int $eventId,
        int $quantity,
        ?string $passDate = null,
        ?string $createdAt = null,
        ?Event $event = null
    ) {
        $this->order_item_id = $orderItemId;
        $this->order_id = $orderId;
        $this->event_id = $eventId;
        $this->quantity = $quantity;
        $this->pass_date = $passDate;
        $this->created_at = $createdAt;
        $this->event = $event;
    }

    public function getPassDateLabel(): string
    {
        $value = trim((string)($this->pass_date ?? ''));
        if ($value === '' || $value === '1000-01-01') {
            return '';
        }

        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if (!$dt instanceof \DateTimeImmutable) {
            return '';
        }

        return $dt->format('l, d M Y');
    }

    public function getUnitPrice(): float
    {
        if (!$this->event instanceof Event) return 0.0;

        return match (strtolower($this->event->event_type)) {
            'jazz' => $this->event instanceof JazzEvent ? $this->event->price : 0.0,
            'dance' => $this->event instanceof DanceEvent ? $this->event->price : 0.0,
            default => $this->event instanceof GenericEvent ? (float) ($this->event->price ?? 0.0) : 0.0,
        };
    }

    public function getLocation(): string
    {
        if (!$this->event instanceof Event) return '';

        return match (strtolower($this->event->event_type)) {
            'jazz' => $this->event instanceof JazzEvent ? $this->event->location : '',
            'dance' => $this->event instanceof DanceEvent ? $this->event->location : '',
            default => $this->event instanceof GenericEvent ? $this->event->location : '',
        };
    }
}
