<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'allocation_id',
    'service_id',
    'percentage',
])]
class AllocationService extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'allocation_id' => 'integer',
            'service_id' => 'integer',
            'percentage' => 'decimal:4',
        ];
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function revenueBreakdowns(): HasMany
    {
        return $this->hasMany(RevenueBreakdown::class);
    }
}
