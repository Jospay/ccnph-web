<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Notifications\GeneralNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MemberShareCapital extends Model implements Payable
{
    protected $fillable = [
        'user_id',
        'status_id',
        'amount',
        'term_months',
    ];

    protected $casts = [
        'amount' => 'integer',
        'term_months' => 'integer',
        'status_id' => 'integer',
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
        return $this->hasMany(ShareCapitalSchedule::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            ShareCapitalSchedule::class,
            'member_share_capital_id',
            'payable_id',
        )->where('payments.payable_type', ShareCapitalSchedule::class);
    }

    public function getTotalPaidAttribute(): int
    {
        return $this->payments()
            ->whereIn('payments.status_id', [Status::PAID, Status::SUCCESS])
            ->sum('payments.amount');
    }

    public function isFullyPaid(): bool
    {
        return $this->total_paid >= $this->amount;
    }

    public function tryActivate(): void
    {
        $hasUnpaid = $this->schedules()
            ->where('status_id', '!=', Status::PAID)
            ->exists();

        if ($hasUnpaid) {
            return;
        }

        $this->update(['status_id' => Status::PAID]);
    }

    public function onPaymentSuccess(Payment $payment): void
    {
        if ($this->isFullyPaid()) {
            $this->user->notify(new GeneralNotification(
                type: 'share_capital_fully_paid',
                title: 'Congratulations! Share Capital Completed',
                body: 'Your share capital has been fully paid! You are now eligible to apply for loans.',
                actionType: 'APPLY_LOAN',
                route: '/(loan)/',
                extraData: [
                    'share_capital_id' => $this->id,
                    'status' => 'fully_paid',
                ]
            ));
        }
    }

    public function onPaymentFailed(Payment $payment): void
    {
        // nothing to do
    }
}
