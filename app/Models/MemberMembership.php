<?php

// app/Models/MemberMembership.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class MemberMembership extends Model
{
    protected $fillable = [
        'user_id',
        'status_id',
        'amount',
        'term_months',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(MembershipSchedule::class);
    }

    // reach payments through schedules
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            MembershipSchedule::class,
            'member_membership_id',
            'payable_id',
        )->where('payments.payable_type', MembershipSchedule::class);
    }

    // called by MembershipSchedule::onPaymentSuccess after each paid schedule
    public function tryActivate(): void
    {
        $schedules = $this->schedules();

        $hasSchedules = $schedules->exists();
        $hasUnpaid = $schedules->where('status_id', '!=', Status::PAID)->exists();

        if (!$hasSchedules || $hasUnpaid) {
            return;
        }

        DB::transaction(function () {
            $this->update([
                'status_id' => Status::ACTIVE,
                'activated_at' => now(),
                'expires_at' => now()->addYear(),
            ]);

            $this->user->update([
                'status_id' => Status::ACTIVE,
                'user_type_id' => UserType::MEMBER,
            ]);
        });
    }
}
