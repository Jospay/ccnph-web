<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'required_amount',
    'allowed_term_months',
])]
class ShareCapitalSetting extends Model
{
    /** @use HasFactory<\Database\Factories\ShareCapitalSettingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'required_amount' => 'integer',
            'allowed_term_months' => 'array',
        ];
    }

    public static function getLatest(): static|null
    {
        return static::latest()->first();
    }
}
