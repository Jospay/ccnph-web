<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'gateway_type'];

    public const CASH = 1;

    public const CARD = 2;

    public const QR_CODE = 3;

    public const MAYA = 4;

    public const BILLEASE = 5;

    public const GRAB_PAY = 6;

    public const DOB = 7;

    public const WALLET = 8;

    public const CASH_ON_DELIVERY = 9;

    public const PAY_ONLINE = 10;

    public const POINT = 11;

    public const CLIENT_SIDE_METHODS = [self::CARD];

    public const OFFLINE_METHODS = [self::CASH, self::WALLET];

    // Third-party (PayMongo) methods used by the store checkout
    public const ONLINE_METHODS = [
        self::QR_CODE,
        self::MAYA,
        self::BILLEASE,
        self::GRAB_PAY,
    ];

    // Everything the store checkout screen may show
    public const STORE_CHECKOUT_METHODS = [
        self::CASH_ON_DELIVERY,
        self::POINT,
        self::QR_CODE,
        self::MAYA,
        self::BILLEASE,
        self::GRAB_PAY,
    ];

    public function isOffline(): bool
    {
        return in_array($this->id, self::OFFLINE_METHODS);
    }

    public function isClientSide(): bool
    {
        return in_array($this->id, self::CLIENT_SIDE_METHODS);
    }

    public function isOnline(): bool
    {
        return in_array($this->id, self::ONLINE_METHODS);
    }
}
