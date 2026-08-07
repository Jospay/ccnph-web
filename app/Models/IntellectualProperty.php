<?php

namespace App\Models;

use App\Notifications\GeneralNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class IntellectualProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(IntellectualPropertyClaim::class);
    }

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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function conversation(): MorphOne
    {
        return $this->morphOne(Conversation::class, 'conversable');
    }

    /**
     * Check if all schedules are paid, then activate and register the IP.
     */
    public function tryActivate(): void
    {
        $hasUnpaid = $this->schedules()
            ->where('status_id', '!=', Status::PAID)
            ->exists();

        if (! $hasUnpaid) {
            $this->update([
                'status_id' => Status::REGISTERED,
                'activated_at' => now(),
                'expires_at' => now()->addYear(),
            ]);

            // Notify user that payment is complete and IP is fully registered
            $this->user?->notify(new GeneralNotification(
                type: 'ip_registered',
                title: 'Application Registered',
                body: "Congrats! Your Intellectual Property application ({$this->title}) is now fully paid and registered.",
                actionType: 'VIEW_PROPERTY',
                route: "/(intellectual)/details?id={$this->id}",
                extraData: [
                    'property_id' => $this->id,
                    'form_type' => $this->form_type,
                    'status' => 'registered',
                ]
            ));
        }
    }
}
