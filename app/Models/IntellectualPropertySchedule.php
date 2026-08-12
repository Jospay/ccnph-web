<?php

namespace App\Models;

use App\Contracts\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class IntellectualPropertySchedule extends Model implements Payable
{
    protected $fillable = [
        'intellectual_property_id',
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

    public function intellectualProperty(): BelongsTo
    {
        return $this->belongsTo(IntellectualProperty::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    // Payable contract — mark itself paid then bubble up
    public function onPaymentSuccess(Payment $payment): void
    {
        $this->update(['status_id' => Status::PAID]);
        $this->intellectualProperty->tryActivate();
    }

    public function onPaymentFailed(Payment $payment): void
    {
        //
    }

    public function cooperativeServiceSlug(): ?string
    {
        return 'intellectual-property-assistance'; // Return your slug string or null
    }
}
