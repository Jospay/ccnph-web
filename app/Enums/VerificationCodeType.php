<?php

namespace App\Enums;

enum VerificationCodeType: string
{
    case PHONE = 'phone';
    case EMAIL = 'email';

    public function label(): string
    {
        return match ($this) {
            self::PHONE => 'Phone',
            self::EMAIL => 'Email',
        };
    }
}
