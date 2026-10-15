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
}
