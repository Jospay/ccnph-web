<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'slug',
    'name',
    'description',
])]
class Allocation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            //
        ];
    }

    public function allocationServices(): HasMany
    {
        return $this->hasMany(AllocationService::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'allocation_services')
            ->withPivot('percentage')
            ->withTimestamps();
    }
}
