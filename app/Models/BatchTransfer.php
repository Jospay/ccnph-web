<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class BatchTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'paymongo_batch_id',
        'paymongo_transfer_id',
        'source_account_number',
        'destination_account_number',
        'destination_account_name',
        'destination_account_bic',
        'channel',
        'amount',
        'fee',
        'provider',
        'purpose',
        'remarks',
        'reference_number',
        'status',
        'failure_code',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'raw_response' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function walletTransaction(): MorphOne
    {
        return $this->morphOne(WalletTransaction::class, 'reference');
    }
}
