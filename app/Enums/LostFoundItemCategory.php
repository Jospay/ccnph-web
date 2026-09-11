<?php

namespace App\Enums;

enum LostFoundItemCategory: string
{
    case ELECTRONICS = 'electronics';
    case ID_DOCUMENTS = 'id_documents';
    case WALLET = 'wallet';
    case KEYS = 'keys';
    case JEWELRY = 'jewelry';
    case CLOTHING = 'clothing';
    case BAGS = 'bags';
    case BOOKS = 'books';
    case SCHOOL_SUPPLIES = 'school_supplies';
    case PERSONAL_ITEMS = 'personal_items';
    case SPORTS_EQUIPMENT = 'sports_equipment';
    case VEHICLE = 'vehicle';
    case PETS = 'pets';
    case MONEY = 'money';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ELECTRONICS => 'Electronics',
            self::ID_DOCUMENTS => 'ID Documents',
            self::WALLET => 'Wallet',
            self::KEYS => 'Keys',
            self::JEWELRY => 'Jewelry',
            self::CLOTHING => 'Clothing',
            self::BAGS => 'Bags',
            self::BOOKS => 'Books',
            self::SCHOOL_SUPPLIES => 'School Supplies',
            self::PERSONAL_ITEMS => 'Personal Items',
            self::SPORTS_EQUIPMENT => 'Sports Equipment',
            self::VEHICLE => 'Vehicle',
            self::PETS => 'Pets',
            self::MONEY => 'Money',
            self::OTHER => 'Other',
        };
    }
}
