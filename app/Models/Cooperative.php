<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'logo',
    'primary_color',
    'secondary_color',
])]
class Cooperative extends Model
{
    use HasFactory;

    /**
     * Get the users associated with the cooperative.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
