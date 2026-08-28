<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'first_name',
    'middle_name',
    'last_name',
    'cooperative_id',
    'phone',
    'verification_request_id',
    'verification_token',
    'otp_sent_at',
    'phone_verified',
])]
class PendingRegistration extends Model
{
    protected $hidden = ['verification_token', 'verification_request_id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'otp_sent_at' => 'datetime',
            'phone_verified' => 'boolean',
            'cooperative_id' => 'integer',
        ];
    }
}
