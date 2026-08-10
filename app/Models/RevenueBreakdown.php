<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'allocation_service_id',
    'amount',
])]
class RevenueBreakdown extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'allocation_service_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function allocationService(): BelongsTo
    {
        return $this->belongsTo(AllocationService::class);
    }
}
