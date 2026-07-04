<?php

namespace App\Models;

use App\Policies\ConversationPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[UsePolicy(ConversationPolicy::class)]
class Conversation extends Model
{
    protected $fillable = ['status'];

    public function conversable(): MorphTo
    {
        return $this->morphTo();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function ensureParticipant(User $user, string $role = 'admin'): ConversationParticipant
    {
        return $this->participants()->firstOrCreate(
            ['user_id' => $user->id],
            ['role' => $role]
        );
    }
}
