<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    // protected $fillable = [];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // relationship to users, many to many
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
        ;
    }

    public function admins(): BelongsToMany
    {
        return $this->users()
            ->where('user_type_id', UserType::ADMIN)
            ->where('status_id', Status::ACTIVE);
    }
}
