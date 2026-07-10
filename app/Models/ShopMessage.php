<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShopMessage extends Model
{
    use HasFactory;

    public const SENDER_SHOP = 'shop';
    public const SENDER_USER = 'user';

    protected $fillable = [
        'shop_conversation_id',
        'sender_id',
        'sender_type',
        'body',
        'context_type',
        'context_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ShopConversation::class, 'shop_conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Snapshot of whatever was pinned (Order or Product) when this was sent.
     */
    public function context(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ShopMessageAttachment::class);
    }

    public function isFromShop(): bool
    {
        return $this->sender_type === self::SENDER_SHOP;
    }

    public function isFromUser(): bool
    {
        return $this->sender_type === self::SENDER_USER;
    }
}