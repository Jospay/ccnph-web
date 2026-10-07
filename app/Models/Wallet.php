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
    protected $fillable = [
        'user_id',
        'balance',
        'show',
        'signature',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'show' => 'boolean',
    ];

    /**
     * Compute the HMAC signature for the wallet balance.
     */
    public function generateSignature(): string
    {
        $formattedBalance = number_format((float) $this->balance, 2, '.', '');

        // Use a secret app key to hash the user_id and balance
        return hash_hmac(
            'sha256',
            "{$this->user_id}:{$formattedBalance}",
            config('app.key')
        );
    }

    /**
     * Check if the recorded signature matches the current record.
     */
    public function isTampered(): bool
    {
        // 1. If no signature exists, mark as tampered (or auto-fix if new)
        if (empty($this->signature)) {
            return true;
        }

        // 2. Validate cryptographic signature match
        $expectedSignature = $this->generateSignature();
        if (! hash_equals($expectedSignature, $this->signature)) {
            return true;
        }

        return false;
    }

    protected static function booted(): void
    {
        static::saving(function (Wallet $wallet) {
            $wallet->signature = $wallet->generateSignature();
        });
    }

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

    public function deposit(
        float $amount,
        Model $reference,
        string $description,
        float $fee = 0.00,
        ?string $referenceNumber = null,
        ?string $providerLabel = null,
        ?string $fromName = null,
        ?string $fromAccountNumber = null,
    ): WalletTransaction {
        $this->balance = (float) $this->balance + $amount;
        $this->save();

        $transaction = $this->walletTransactions()->create([
            'reference_type' => $reference::class,
            'reference_id' => $reference->id,
            'reference_number' => $referenceNumber,
            'type' => 'deposit',
            'amount' => $amount,
            'transfer_fee' => $fee,
            'from_name' => $fromName ?? $providerLabel ?? $reference::class,
            'from_account_number' => $fromAccountNumber,
            'to_account_name' => $this->user?->name,
            'to_account_number' => (string) $this->id,
            'to_provider' => $providerLabel,
            'description' => $description,
        ]);

        broadcast(new WalletBalanceUpdated($this->fresh()));

        return $transaction;
    }

    public function withdraw(
        float $amount,
        Model $reference,
        string $description,
        float $fee = 0.00,
        ?string $referenceNumber = null,
        ?string $providerLabel = null
    ): WalletTransaction {
        $this->balance = (float) $this->balance - $amount;
        $this->save(); // Save triggers static::saving to update signature automatically

        $transaction = $this->walletTransactions()->create([
            'reference_type' => $reference::class,
            'reference_id' => $reference->id,
            'reference_number' => $referenceNumber,
            'type' => 'withdrawal',
            'amount' => $amount,
            'transfer_fee' => $fee,
            'from_name' => $this->user?->name,
            'to_account_name' => $providerLabel,
            'to_provider' => $providerLabel,
            'description' => $description,
        ]);

        broadcast(new WalletBalanceUpdated($this->fresh()));

        return $transaction;
    }

    public function onPaymentSuccess(Payment $payment): void
    {
        $amountDecimal = $payment->amount / 100;
        $feeDecimal = ($payment->fee ?? 0) / 100;

        $providerLabel = $payment->paymentMethod?->name ?? ucfirst($payment->gateway);

        $this->deposit(
            $amountDecimal,
            $payment,
            'Wallet recharge via '.$providerLabel,
            $feeDecimal,
            $payment->gateway_payment_intent_id,
            $providerLabel,
            $payment->sender_name,
            $payment->sender_account_number,
        );
    }

    public function onPaymentFailed(Payment $payment): void
    {
        // nothing to do
    }

    public function cooperativeServiceSlug(): ?string
    {
        return 'wallet';
    }

    public function batchTransfers(): HasMany
    {
        return $this->hasMany(BatchTransfer::class);
    }
}
