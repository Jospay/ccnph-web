<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointTransaction extends Model
{
    protected $fillable = [
        'point_id',
        'reference_type',
        'reference_id',
        'reference_number',
        'type',
        'amount',
        'transfer_fee',
        'from_name',
        'from_account_number',
        'to_account_name',
        'to_account_number',
        'to_provider',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_fee' => 'decimal:2',
    ];

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
