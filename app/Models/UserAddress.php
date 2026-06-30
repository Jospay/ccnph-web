<?php

namespace App\Models;

use App\Enums\UserAddressLabel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'label',
    'recipient_name',
    'recipient_number',
    'region',
    'region_code',
    'province',
    'province_code',
    'city',
    'city_code',
    'barangay',
    'barangay_code',
    'street',
    'unit_bldg_house',
    'postal_code',
    'landmark',
    'is_default',
])]
class UserAddress extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'label' => UserAddressLabel::class,
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
