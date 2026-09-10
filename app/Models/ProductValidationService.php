<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\ProductValidationServiceCategory;
use App\Enums\ProductValidationServiceGoal;
use App\Enums\ProductValidationServiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable([
    'product_name',
    'product_category',
    'product_description',
    'validation_goal',
    'target_market',
    'status',
    'estimated_price',
])]
class ProductValidationService extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'product_category' => ProductValidationServiceCategory::class,
            'validation_goal' => ProductValidationServiceGoal::class,
            'status' => ProductValidationServiceStatus::class,
            'estimated_price' => 'decimal:2',
        ];
    }

    
}
