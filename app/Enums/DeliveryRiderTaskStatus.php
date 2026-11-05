<?php

namespace App\Enums;

enum DeliveryRiderTaskStatus: string
{
    case ASSIGNED = 'assigned';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ASSIGNED => 'Assigned',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }
}