<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    protected $fillable = [
        'phone',
        'verification_request_id',
        'verification_token',
        'phone_verified',
        'otp_sent_at',
    ];

    protected $casts = [
        'phone_verified' => 'boolean',
        'otp_sent_at' => 'datetime',
    ];
}
