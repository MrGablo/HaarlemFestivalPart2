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
    public ?float $unit_price_override;
    public ?float $line_total_override;

    public function __construct(
        int $orderItemId,
        int $orderId,
        int $eventId,
        int $quantity,
        ?string $passDate = null,
        ?string $createdAt = null,
        ?Event $event = null,
        ?float $unitPriceOverride = null,
        ?float $lineTotalOverride = null
    ) {
        $this->order_item_id = $orderItemId;
        $this->order_id = $orderId;
        $this->event_id = $eventId;
        $this->quantity = $quantity;
        $this->pass_date = $passDate;
        $this->created_at = $createdAt;
        $this->event = $event;
        $this->unit_price_override = $unitPriceOverride;
        $this->line_total_override = $lineTotalOverride;
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
        if ($this->unit_price_override !== null) {
            return $this->unit_price_override;
        }

        if (!$this->event instanceof Event) return 0.0;

        return match (strtolower($this->event->event_type)) {
            'jazz' => $this->event instanceof JazzEvent ? $this->event->price : 0.0,
            'dance' => $this->event instanceof DanceEvent ? $this->event->price : 0.0,
            default => $this->event instanceof GenericEvent ? (float) ($this->event->price ?? 0.0) : 0.0,
        };
    }

    public function getTotalPrice(): float
    {
        if ($this->line_total_override !== null) {
            return $this->line_total_override;
        }

        return $this->getUnitPrice() * max(0, (int)$this->quantity);
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
