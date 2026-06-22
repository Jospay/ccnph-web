<?php

namespace App\Models;

use App\Contracts\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Wallet extends Model implements Payable
{
    protected $fillable = ['user_id', 'balance', 'show'];

    protected $casts = [
        'balance' => 'decimal:2',
        'show' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function onPaymentSuccess(Payment $payment): void
    {
        $amountDecimal = $payment->amount / 100;

        $this->increment('balance', $amountDecimal);

        $this->walletTransactions()->create([
            'reference_type' => Payment::class,
            'reference_id' => $payment->id,
            'type' => 'deposit',
            'amount' => $amountDecimal,
            'description' => 'Wallet recharge via ' . $payment->gateway,
        ]);
    }

    public function onPaymentFailed(Payment $payment): void
    {
        // nothing to do
    }
}
