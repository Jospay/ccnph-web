<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'shop_id',
    'name',
    'slug',
    'is_active',
    'is_featured',
    'description',
    'video',
    'views',
    'rating',
    'reviews_count',
    'sold_count',
])]
class Product extends Model
{
    use HasFactory;

    /**
     * Use the slug column for Route Model Binding.
     * Prevents 404 errors when passing slug strings instead of database IDs.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'views' => 'integer',
            'rating' => 'float',
            'reviews_count' => 'integer',
            'sold_count' => 'integer',
        ];
    }

    /**
     * Named 'store' to cleanly align with eager loading execution statements
     * and ProductShowResource definitions.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)
            ->where('is_default', true);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
