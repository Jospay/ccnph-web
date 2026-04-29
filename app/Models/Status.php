<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    public $timestamps = false;

    public const ACTIVE = 1;
    public const APPROVED = 2;
    public const REJECTED = 3;
    public const CANCELLED = 4;
    public const PENDING = 5;
    public const FINISHED = 6;
    public const PAID = 7;
    public const UNPAID = 8;
    public const OVERDUE = 9;
    public const FOR_APPROVAL = 10;

    public const SUCCESS = 11;

    public const FAILED = 12;

    public const ARCHIVED = 13;

    public const WAITING_FOR_PAYMENT = 14;

    public const REGISTERED = 15;
    public const EXPIRED = 16;

    // protected $fillable = [];

    // one to many, status has many loans
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // one to many, status has many loan schedules
    public function loanSchedules(): HasMany
    {
        return $this->hasMany(LoanSchedule::class);
    }

    // one to many, status has many users
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // one to many, status has many intellectual properties
    public function intellectualProperties(): HasMany
    {
        return $this->hasMany(IntellectualProperty::class);
    }
}
