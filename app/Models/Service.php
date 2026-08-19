<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    // protected $fillable = [];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relationship to users, many to many
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->users()
            ->where('user_type_id', UserType::ADMIN)
            ->where('status_id', Status::ACTIVE);
    }

    public function allocationServices(): HasMany
    {
        return $this->hasMany(AllocationService::class);
    }

    public function allocations(): BelongsToMany
    {
        return $this->belongsToMany(Allocation::class, 'allocation_services')
            ->withPivot(['value', 'priority', 'type']) // Updated to match AllocationService columns
            ->withTimestamps();
    }
}
