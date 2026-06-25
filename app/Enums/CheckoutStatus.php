<?php

namespace App\Enums;

enum CheckoutStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'Pending Payment',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
        };
    }
}
