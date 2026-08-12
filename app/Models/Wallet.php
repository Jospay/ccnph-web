<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Events\WalletBalanceUpdated;
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

    /**
     * Credit the wallet, log the transaction, and broadcast the new balance.
     * Central entry point for ANY wallet credit (loans, recharges, refunds, etc).
     */
    public function deposit(float $amount, Model $reference, string $description): WalletTransaction
    {
        $this->increment('balance', $amount);

        $transaction = $this->walletTransactions()->create([
            'reference_type' => $reference::class,
            'reference_id' => $reference->id,
            'type' => 'deposit',
            'amount' => $amount,
            'description' => $description,
        ]);

        broadcast(new WalletBalanceUpdated($this->fresh()));

        return $transaction;
    }

    public function onPaymentSuccess(Payment $payment): void
    {
        $amountDecimal = $payment->amount / 100;

        $this->deposit($amountDecimal, $payment, 'Wallet recharge via '.$payment->gateway);
    }

    public function onPaymentFailed(Payment $payment): void
    {
        // nothing to do
    }

    public function cooperativeServiceSlug(): ?string
    {
        return 'wallet';
    }
}
