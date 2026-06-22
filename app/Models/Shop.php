<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
])]
class Shop extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_official' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($shop) {
            $user = $shop->user; 
            if (!$user || $user->user_type_id !== UserType::MEMBER) {
                throw new InvalidArgumentException('Only members can have a shop.');
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
