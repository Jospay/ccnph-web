<?php

namespace App\Models;

use App\Policies\ShopConversationPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[UsePolicy(ShopConversationPolicy::class)]
class ShopConversation extends Model
{
    protected $fillable = [
        'shop_id',
        'user_id',
        'pinnable_type',
        'pinnable_id',
        'pinned_at',
        'last_message_at',
        'shop_read_at',
        'user_read_at',
    ];

    protected function casts(): array
    {
        return [
            'pinned_at' => 'datetime',
            'last_message_at' => 'datetime',
            'shop_read_at' => 'datetime',
            'user_read_at' => 'datetime',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The currently pinned context — an Order or a Product.
     */
    public function pinnable(): MorphTo
    {
        return $this->morphTo();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ShopMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ShopMessage::class)->latestOfMany();
    }

    /**
     * Pin an order or product as the active context for this conversation.
     */
    public function pin(Model $context): void
    {
        $this->update([
            'pinnable_type' => $context::class,
            'pinnable_id' => $context->id,
            'pinned_at' => now(),
        ]);
    }

    /**
     * Clear the pinned context (e.g. order completed/cancelled).
     */
    public function unpin(): void
    {
        $this->update([
            'pinnable_type' => null,
            'pinnable_id' => null,
            'pinned_at' => null,
        ]);
    }

    public function hasUnreadForShop(): bool
    {
        if (! $this->last_message_at) {
            return false;
        }

        return ! $this->shop_read_at || $this->shop_read_at->lt($this->last_message_at);
    }

    public function hasUnreadForUser(): bool
    {
        if (! $this->last_message_at) {
            return false;
        }

        return ! $this->user_read_at || $this->user_read_at->lt($this->last_message_at);
    }
}
