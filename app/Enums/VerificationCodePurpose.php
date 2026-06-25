<?php

namespace App\Enums;

enum VerificationCodePurpose: string
{
    case REGISTRATION = 'registration';
    case CHANGE_PHONE = 'change_phone';
    case CHANGE_EMAIL = 'change_email';
    case FORGOT_PASSWORD = 'forgot_password';

    public function label(): string
    {
        return match ($this) {
            self::REGISTRATION => 'Registration',
            self::CHANGE_PHONE => 'Change Phone',
            self::CHANGE_EMAIL => 'Change Email',
            self::FORGOT_PASSWORD => 'Forgot Password',
        };
    }
}
