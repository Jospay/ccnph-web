<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case ASSIGNED = 'assigned';
    case PICKED_UP = 'picked_up';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::ASSIGNED => 'Assigned',
            self::PICKED_UP => 'Picked up / Out for delivery',
            self::DELIVERED => 'Delivered',
            self::FAILED => 'Failed',
        };
    }

    /** Statuses where the driver still has work to do. */
    public static function active(): array
    {
        return [self::ASSIGNED, self::PICKED_UP];
    }

    /** Statuses where admin may still change the driver. */
    public static function reassignable(): array
    {
        return [self::ASSIGNED, self::FAILED];
    }
}
