<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'region',
        'region_code',
        'province',
        'province_code',
        'city',
        'city_code',
        'barangay',
        'barangay_code',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** Lower-case + trim so "Angeles City" == " angeles city ". */
    public static function normalize(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    /**
     * Does this area cover the address of the given order?
     * Whole-city area (barangay = null) covers every barangay of that city.
     */
    public function covers(Order $order): bool
    {
        if (self::normalize($this->city) !== self::normalize($order->city)) {
            return false;
        }

        if ($this->province && $order->province
            && self::normalize($this->province) !== self::normalize($order->province)) {
            return false;
        }

        if ($this->barangay === null || $this->barangay === '') {
            return true;
        }

        return self::normalize($this->barangay) === self::normalize($order->barangay);
    }
}
