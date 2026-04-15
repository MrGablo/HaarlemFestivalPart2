<?php

namespace App\Models;

class Payment
{
    public int $order_id;
    public int $user_id;
    public OrderStatus $order_status;
    public ?string $payment_deadline_at;

    public function __construct(
        int $orderId,
        int $userId,
        OrderStatus $orderStatus,
        ?string $paymentDeadlineAt = null
    ) {
        $this->order_id = $orderId;
        $this->user_id = $userId;
        $this->order_status = $orderStatus;
        $this->payment_deadline_at = $paymentDeadlineAt;
    }
}
