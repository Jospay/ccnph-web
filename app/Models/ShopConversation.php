<?php

namespace App\Models;

use App\Policies\ShopConversationPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UsePolicy(ShopConversationPolicy::class)]
class ShopConversation extends Model
{
    protected $fillable = ['buyer_id', 'seller_id', 'pinned_product_id'];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function pinnedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'pinned_product_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ShopMessage::class);
    }

    public static function findOrStartBetween(int $buyerId, int $sellerId, ?int $productId = null): self
    {
        $conversation = static::firstOrCreate([
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
        ]);

        if ($productId) {
            $conversation->update(['pinned_product_id' => $productId]);
        }

        return $conversation;
    }

    public function otherParticipant(User $user): User
    {
        return $user->id === $this->buyer_id ? $this->seller : $this->buyer;
    }
}
