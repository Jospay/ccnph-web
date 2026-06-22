<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductVariantAttributeValue extends Pivot
{
    protected $table = 'product_variant_attribute_values';

    public $incrementing = true;
    
    protected $fillable = [
        'product_variant_id',
        'attribute_value_id',
    ];
}