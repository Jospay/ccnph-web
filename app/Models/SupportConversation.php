<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class SupportConversation extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): MorphOne
    {
        return $this->morphOne(Conversation::class, 'conversable');
    }
}
