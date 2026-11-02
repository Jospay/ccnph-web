<?php

namespace App\Enums;

enum DeliverySettlementStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
        };
    }
}