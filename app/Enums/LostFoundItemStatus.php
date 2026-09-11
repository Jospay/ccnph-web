<?php

namespace App\Enums;

enum LostFoundItemStatus: string
{
    case ACTIVE = 'active';
    case CLAIMED = 'claimed';
    case RESOLVED = 'resolved';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::CLAIMED => 'Claimed',
            self::RESOLVED => 'Resolved',
            self::CANCELLED => 'Cancelled',
        };
    }
}
