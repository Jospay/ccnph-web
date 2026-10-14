<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Events\PointBalanceUpdated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

class Point extends Model implements Payable
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

    public function generateSignature(): string
    {
        $formattedBalance = number_format((float) $this->balance, 2, '.', '');

        return hash_hmac(
            'sha256',
            "{$this->user_id}:{$formattedBalance}",
            config('app.key')
        );
    }

    public function isTampered(): bool
    {
        if (empty($this->signature)) {
            return true;
        }

        return ! hash_equals($this->generateSignature(), $this->signature);
    }

    protected static function booted(): void
    {
        static::saving(function (Point $point) {
            $point->signature = $point->generateSignature();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
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
    ): PointTransaction {
        // FIX 1: atomic balance + ledger row
        $transaction = DB::transaction(function () use (
            $amount, $reference, $description, $fee, $referenceNumber,
            $providerLabel, $fromName, $fromAccountNumber
        ) {
            $this->balance = (float) $this->balance + $amount;
            $this->save();

            return $this->pointTransactions()->create([
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
        });

        broadcast(new PointBalanceUpdated($this->fresh()));

        return $transaction;
    }

    public function withdraw(
        float $amount,
        Model $reference,
        string $description,
        float $fee = 0.00,
        ?string $referenceNumber = null,
        ?string $providerLabel = null
    ): PointTransaction {
        $transaction = DB::transaction(function () use (
            $amount, $reference, $description, $fee, $referenceNumber, $providerLabel
        ) {
            $this->balance = (float) $this->balance - $amount;
            $this->save();

            return $this->pointTransactions()->create([
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
        });

        broadcast(new PointBalanceUpdated($this->fresh()));

        return $transaction;
    }

    public function onPaymentSuccess(Payment $payment): void
    {
        // FIX 2: idempotency guard
        $alreadyCredited = $this->pointTransactions()
            ->where('reference_type', $payment::class)
            ->where('reference_id', $payment->id)
            ->exists();

        if ($alreadyCredited) {
            return;
        }

        $amountDecimal = $payment->amount / 100;
        $feeDecimal = ($payment->fee ?? 0) / 100;

        $providerLabel = $payment->paymentMethod?->name ?? ucfirst($payment->gateway);

        $this->deposit(
            $amountDecimal,
            $payment,
            'Point recharge via '.$providerLabel,
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
        return null;
    }

    public function batchTransfers(): HasMany
    {
        return $this->hasMany(BatchTransfer::class);
    }

    public function cooperativeId(): ?int
    {
        $this->loadMissing('user');

        return $this->user?->cooperative_id;
    }
}
