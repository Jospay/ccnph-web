<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanSetting extends Model
{
    protected $fillable = [
        'user_id',
        'default_amount',
        'default_interest_rate',
        'default_term_months',
    ];

    // one to many, loan setting has one user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
