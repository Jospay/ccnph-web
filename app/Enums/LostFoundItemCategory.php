<?php

namespace App\Enums;

enum LostFoundItemCategory: string
{
    case PHONE = 'phone';
    case WALLET = 'wallet';
    case ID = 'id';
    case BAG = 'bag';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PHONE => 'Phone',
            self::WALLET => 'Wallet',
            self::ID => 'ID',
            self::BAG => 'Bag',
            self::OTHER => 'Other',
        };
    }
}
