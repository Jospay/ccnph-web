<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Enums\CheckoutStatus;
use App\Services\Store\CheckoutService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable([
    'user_id',
    'checkout_number',
    'grand_total',
    'status',
    'cancelled_amount',
])]
class Checkout extends Model implements Payable
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'grand_total' => 'decimal:2',
            'status' => CheckoutStatus::class,
            'cancelled_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // payments.payable_type / payable_id (same link the webhook uses)
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    // Called by PaymentWebhookService
    public function onPaymentSuccess(Payment $payment): void
    {
        app(CheckoutService::class)->markAsPaid($this);
    }

    public function onPaymentFailed(Payment $payment): void
    {
        app(CheckoutService::class)->markAsFailed($this);
    }

    public function cooperativeServiceSlug(): ?string
    {
        return null;
    }

    public function cooperativeId(): ?int
    {
        return $this->user?->cooperative_id;
    }
}
