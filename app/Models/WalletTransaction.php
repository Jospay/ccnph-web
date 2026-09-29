<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'reference_type',
        'reference_id',
        'reference_number',
        'type',
        'amount',
        'transfer_fee',
        'from_name',
        'to_account_name',
        'to_account_number',
        'to_provider',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_fee' => 'decimal:2',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
