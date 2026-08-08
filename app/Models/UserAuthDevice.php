<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'device_id',
    'platform',
    'public_key',
    'biometric_enabled',
    'device_name',
    'last_used_at',
])]
class UserAuthDevice extends Model
{
    /**
     * Get the user that owns this authentication device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'biometric_enabled' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }
}
