<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Casts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

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
class Store extends Model
{
    protected $table = 'shops';
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_official' => 'boolean',
        ];
    }
    
    protected $appends = [
        'logo_url',
        'banner_url',
    ];

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo
                ? Storage::url($this->logo)
                : null,
        );
    }
    protected function bannerUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->banner
                ? Storage::url($this->banner)
                : null,
        );
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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}