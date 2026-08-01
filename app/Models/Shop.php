<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

#[Fillable([
    'user_id',
    'name',
    'slug',
    'is_active',
    'is_official',
    'description',
    'logo',
    'banner',
    'rating',
    'reviews_count',
    'sold_count',
])]
class Shop extends Model
{
    use HasFactory;

    /**
     * Use the shop slug for implicit route model binding.
     *
     * Example:
     * GET /api/store/my-shop
     * will resolve using:
     * SELECT * FROM shops WHERE slug = 'my-shop'
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_official' => 'boolean',
            'rating' => 'float',
            'reviews_count' => 'integer',
            'sold_count' => 'integer',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($shop) {
            $user = $shop->user;

            if (! $user || $user->user_type_id !== UserType::MEMBER) {
                throw new InvalidArgumentException('Only members can have a shop.');
            }
        });
    }

    /**
     * Shop owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Products that belong to this shop.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Orders belonging to this shop.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Reviews belonging to this shop.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Recalculate and save the shop's overall rating based on all its reviews.
     */
    public function updateRating(): void
    {
        $average = $this->reviews()->avg('rating') ?? 0;

        $this->update([
            'rating' => round((float) $average, 1),
        ]);
    }

    public function followers(): HasMany
    {
        return $this->hasMany(ShopFollower::class);
    }
}
