<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopMessageAttachment extends Model
{
    protected $fillable = [
        'shop_message_id',
        'path',
        'original_name',
        'mime_type',
        'size'
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ShopMessage::class);
    }
}
