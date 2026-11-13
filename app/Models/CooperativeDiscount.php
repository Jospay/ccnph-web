<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CooperativeDiscount extends Model
{
    protected $fillable = [
        'module',
        'type',
        'discount_value',
    ];

    protected $casts = [
        'discount_value' => 'decimal:4',
    ];

    public const TYPE_PERCENTAGE = 'Percentage';

    public const TYPE_PHP = 'PHP';

    public const MODULE_MEMBERSHIP = 'Membership';

    public const MODULE_SHARED_CAPITAL = 'Shared Capital';

    public const MODULE_POINT = 'Point';

    public static function forModule(string $module): ?self
    {
        return static::where('module', $module)->first();
    }

    /**
     * Discount for a given amount (never more than the amount itself).
     */
    public function calculate(float $amount): float
    {
        $value = (float) $this->discount_value;

        $discount = $this->type === self::TYPE_PERCENTAGE
            ? $amount * ($value / 100)
            : $value;

        return round(min(max($discount, 0), $amount), 2);
    }
}
