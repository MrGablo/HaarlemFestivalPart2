<?php

namespace App\Models;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAYED = 'payed';
    case CANCELLED = 'cancelled';
}
