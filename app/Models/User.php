<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'user_type_id',
    'status_id',
    'is_seller',
    'phone',
    'phone_verified_at',
    'gender',
    'birthdate',
    'region',
    'province',
    'city',
    'barangay',
    'street',
    'postal_code',
    'avatar',
    'valid_id_type',
    'valid_id_number',
    'front_valid_id_picture',
    'back_valid_id_picture',
])]

#[Hidden([
    'password',
    'remember_token',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'valid_id_number',
    'front_valid_id_picture',
    'back_valid_id_picture',
])]
class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',

            'birthdate' => 'date:Y-m-d',
            'is_seller' => 'boolean',

            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($user) {
            if ($user->is_seller && $user->user_type_id !== UserType::MEMBER) {
                throw new InvalidArgumentException('Only members can be sellers.');
            }
        });
    }

    // relationship to user type, one to many
    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    // relationship to services, many to many
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->withTimestamps();
    }

    // relationship to loans, one to many
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // relationship to loan settings, one to many
    public function loanSettings(): HasMany
    {
        return $this->hasMany(LoanSetting::class);
    }

    // relationship to status, one to many
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // relationship to wallet, one to many
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(Checkout::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // relationship to intellectual properties, one to many
    public function intellectualProperties(): HasMany
    {
        return $this->hasMany(IntellectualProperty::class);
    }

    public function shareCapital(): HasOne
    {
        return $this->hasOne(MemberShareCapital::class);
    }

    // checker for service management
    public function managesService(int $serviceId): bool
    {
        // Super Admins bypass
        if ($this->user_type_id === UserType::SUPER_ADMIN && $this->status_id === Status::ACTIVE) {
            return true;
        }

        // Must be an Admin and currently Active
        if ($this->user_type_id !== UserType::ADMIN || $this->status_id !== Status::ACTIVE) {
            return false;
        }

        // Must be assigned to the service AND the service must be active
        return $this->services()
            ->where('services.id', $serviceId)
            ->where('services.is_active', true)
            ->exists();
    }

    // instance method to get active loan setting
    // public function getActiveLoanSetting()
    // {
    //     return $this->loanSettings()
    //         ->orWhereNull('user_id')
    //         ->orderByRaw('user_id DESC')
    //         ->first();
    // }

    // public function getActiveLoanSetting()
    // {
    //     return LoanSetting::where(function ($query) {
    //         $query->where('user_id', $this->id)
    //             ->orWhereNull('user_id');
    //     })
    //         ->orderByRaw('user_id IS NULL')
    //         ->first();
    // }

    public function getActiveLoanSetting(): LoanSetting|int
    {
        $shareCapital = $this->shareCapital;

        if (! $shareCapital || ! $shareCapital->isFullyPaid()) {
            return 0;
        }

        return LoanSetting::where(function ($query) {
            $query->where('user_id', $this->id)
                ->orWhereNull('user_id');
        })
            ->orderByRaw('user_id IS NULL')
            ->first();
    }

    public function membership(): HasOne
    {
        return $this->hasOne(MemberMembership::class);
    }

    public function hasActiveMembership(): bool
    {
        return $this->membership()
            ->where('status_id', Status::ACTIVE)
            ->exists();
    }

    public function authDevices(): HasMany
    {
        return $this->hasMany(UserAuthDevice::class);
    }
}
