<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'contact_number',
        'password',
        'photo',
        'is_active',
        'last_login_at',
        'created_by',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->photo ? Storage::url($this->photo) : null);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(DriverArea::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(OrderDelivery::class);
    }

    public function activeDeliveries(): HasMany
    {
        return $this->deliveries()->whereIn('status', array_map(
            fn (DeliveryStatus $s) => $s->value,
            DeliveryStatus::active()
        ));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
