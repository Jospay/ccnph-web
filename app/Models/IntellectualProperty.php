<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IntellectualProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status_id',
        'amount',
        'term_months',
        'creation_type',
        'form_type',
        'title',
        'description',
        'applicability',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'status_id' => 'integer',
        'amount' => 'integer',
        'term_months' => 'integer',
        'activated_at' => 'date',
        'expires_at' => 'date',
    ];

    // one to many, intellectual property has one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // one to many, intellectual property has one status
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // one to many, intellectual property has one payment
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    // one to many, intellectual property has many claims
    public function claims(): HasMany
    {
        return $this->hasMany(IntellectualPropertyClaim::class);
    }

    // one to many, intellectual property has many documents
    public function documents(): HasMany
    {
        return $this->hasMany(IntellectualPropertyDocument::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(IntellectualPropertySchedule::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(IntellectualPropertySetting::class);
    }

    public function tryActivate(): void
    {
        $hasUnpaid = $this->schedules()
            ->where('status_id', '!=', Status::PAID)
            ->exists();

        if (!$hasUnpaid) {
            $this->update([
                'status_id' => Status::REGISTERED,
                'activated_at' => now(),
                'expires_at' => now()->addYear(),
            ]);
        }
    }
}
