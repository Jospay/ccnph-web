<?php

namespace App\Models;

use App\Contracts\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ShareCapitalSchedule extends Model implements Payable
{
    protected $fillable = [
        'member_share_capital_id',
        'status_id',
        'installment_no',
        'amount',
        'due_date',
    ];

    protected $casts = [
        'amount' => 'integer',
        'due_date' => 'date',
        'status_id' => 'integer',
    ];

    public function shareCapital(): BelongsTo
    {
        return $this->belongsTo(MemberShareCapital::class, 'member_share_capital_id');
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

        $this->shareCapital->tryActivate();
    }

    public function onPaymentFailed(Payment $payment): void
    {
        // schedule stays unpaid, nothing to do
    }

    public function cooperativeServiceSlug(): ?string
    {
        return 'share-capital';
    }
}
