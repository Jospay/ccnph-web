<?php

namespace App\Models;

use App\Exceptions\IntellectualProperty\IntellectualPropertySettingsNotFoundException;
use Illuminate\Database\Eloquent\Model;

class IntellectualPropertySetting extends Model
{
    protected $fillable = [
        'amount',
        'allowed_term_months',
    ];

    protected $casts = [
        'amount' => 'integer',
        'allowed_term_months' => 'array',
    ];

    public static function current(IntellectualProperty $ip): self
    {
        return $ip->setting
            ?? throw new IntellectualPropertySettingsNotFoundException();
    }
}
