<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'order_item_id',
    'shop_id',
    'product_id',
    'rating',
    'comment',
    'video',
    'is_anonymous',
    'reply',
    'replied_at',
])]
class Review extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'order_item_id' => 'integer',
            'shop_id' => 'integer',
            'product_id' => 'integer',
            'is_anonymous' => 'boolean',
            'rating' => 'integer',
            'replied_at' => 'datetime',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
