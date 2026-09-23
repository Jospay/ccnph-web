<?php

namespace App\Enums;

enum CooperativeScope: string
{
    case LOCAL = 'local';
    case NATIONAL = 'national';

    public function label(): string
    {
        return match ($this) {
            self::LOCAL => 'Local',
            self::NATIONAL => 'National',
        };
    }
}
