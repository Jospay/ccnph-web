<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionFee extends Model
{
    protected $fillable = [
        'module',
        'type',
        'transfer_fee',
        'minimum_fee',
    ];

    protected $casts = [
        'transfer_fee' => 'decimal:4',
        'minimum_fee' => 'decimal:2',
    ];

    public const TYPE_PERCENTAGE = 'Percentage';

    public const TYPE_PHP = 'PHP';

    public static function forModule(string $module): ?self
    {
        return static::where('module', $module)->first();
    }

    public function calculate(float $amount): float
    {
        $fee = $this->type === self::TYPE_PERCENTAGE
            ? $amount * ((float) $this->transfer_fee / 100)
            : (float) $this->transfer_fee;

        return round($fee, 2);
    }
}
