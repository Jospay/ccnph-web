<?php

namespace App\Enums;

enum LostFoundItemType: string
{
    case LOST = 'lost';
    case FOUND = 'found';

    public function label(): string
    {
        return match ($this) {
            self::LOST => 'Lost',
            self::FOUND => 'Found',
        };
    }
}
