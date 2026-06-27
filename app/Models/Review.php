<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id',
    'order_item_id',
    'shop_id',
    'product_id',
    'rating',
    'review',
    'video',
    'is_anonymous',
])]
class Review extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'rating' => 'integer',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}