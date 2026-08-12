<?php

namespace App\Models;

use App\Contracts\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MembershipSchedule extends Model implements Payable
{
    protected $fillable = [
        'member_membership_id',
        'status_id',
        'installment_no',
        'amount',
        'due_date',
    ];

    protected $casts = [
        'status_id' => 'integer',
        'amount' => 'integer',
        'due_date' => 'date',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(MemberMembership::class, 'member_membership_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function onPaymentSuccess(Payment $payment): void
    {
        $this->update(['status_id' => Status::PAID]);
        $this->membership->tryActivate();
    }

    public function onPaymentFailed(Payment $payment): void
    {
        //
    }

    public function cooperativeServiceSlug(): ?string
    {
        return 'coop-membership';
    }
}
