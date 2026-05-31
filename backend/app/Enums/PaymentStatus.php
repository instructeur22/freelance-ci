<?php
namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Held = 'held';
    case Released = 'released';
    case Refunded = 'refunded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
