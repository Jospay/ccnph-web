<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public const CLIENT_SIDE_METHODS = [
        self::CARD,
    ];

    public const OFFLINE_METHODS = [
        self::CASH,
        self::WALLET,
    ];

    public function isOffline(): bool
    {
        return in_array($this->id, self::OFFLINE_METHODS);
    }

    public function isClientSide(): bool
    {
        return in_array($this->id, self::CLIENT_SIDE_METHODS);
    }
}
