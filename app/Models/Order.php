<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'shop_id',
    'checkout_id',
    'order_number',
    'status',
    'subtotal',
    'shipping_fee',
    'discount',
    'total',
    'notes',
    'recipient_name',
    'recipient_phone',
    'region',
    'province',
    'city',
    'barangay',
    'street',
    'unit_bldg_house',
    'postal_code',
    'landmark',
])]
class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'shop_id' => 'integer',
            'checkout_id' => 'integer',
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Renamed to store to match frontend services and API Resources.
     * Explicitly uses 'shop_id' as the foreign key matching your fillables.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function checkout(): BelongsTo
    {
        return $this->belongsTo(Checkout::class);
    }

    // cod helper method
    public function isCod(): bool
    {
        return $this->checkout?->payment?->payment_method_id === PaymentMethod::CASH_ON_DELIVERY;
    }

    // online helper method
    public function isOnline(): bool
    {
        return $this->checkout?->payment?->payment_method_id === PaymentMethod::PAY_ONLINE;
    }
}
