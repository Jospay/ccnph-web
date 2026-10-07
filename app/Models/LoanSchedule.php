<?php

namespace App\Models;

use App\Contracts\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LoanSchedule extends Model implements Payable
{
    protected $fillable = [
        'loan_id',
        'status_id',
        'month_no',
        'beginning_balance',
        'interest_amount',
        'principal_amount',
        'total_payment',
        'ending_balance',
        'due_date',
    ];

    protected $casts = [
        'status_id' => 'integer',
        'due_date' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    // Payable contract — this
    public function onPaymentSuccess(Payment $payment): void
    {
        $this->update(['status_id' => Status::PAID]);
        $this->loan->tryFinish();
    }

    public function onPaymentFailed(Payment $payment): void
    {
        //
    }

    public function cooperativeServiceSlug(): ?string
    {
        return 'loan-assistance';
    }
}
