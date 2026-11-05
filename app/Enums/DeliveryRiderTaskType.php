<?php

namespace App\Enums;

enum DeliveryRiderTaskType: string
{
    case PICKUP = 'pickup';
    case DELIVERY = 'delivery';
    case RETURN = 'return';

    public function label(): string
    {
        return match ($this) {
            self::PICKUP => 'Pickup',
            self::DELIVERY => 'Delivery',
            self::RETURN => 'Return',
        };
    }
}