<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'payable_type',
    'payable_id',
    'payment_method_id',
    'checkout_id',
    'status_id',
    'payment_date',
    'amount',
    'cancelled_amount',
    'gateway',
    'gateway_payment_intent_id',
    'gateway_payment_id',
    'gateway_response',
])]
class Payment extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_id' => 'integer',
            'checkout_id' => 'integer',
            'payment_method_id' => 'integer',
            'amount' => 'integer',
            'cancelled_amount' => 'integer',
            'payment_date' => 'date',
            'gateway_response' => 'array',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function walletTransactions(): MorphMany
    {
        return $this->morphMany(WalletTransaction::class, 'reference');
    }

    public function gatewayLogs(): HasMany
    {
        return $this->hasMany(PaymentGatewayLog::class);
    }

    public function intellectualProperties(): HasMany
    {
        return $this->hasMany(IntellectualProperty::class);
    }

    public function checkout(): BelongsTo
    {
        return $this->belongsTo(Checkout::class);
    }
}
