<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'name',
    'api_key_hash',
    'webhook_url',
    'webhook_secret',
    'is_active',
])]
class DeliveryApiClient extends Model
{
    protected $hidden = [
        'api_key_hash', 
        'webhook_secret'
    ];

    protected function casts(): array
    {
        return [
            'webhook_secret' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(DeliveryShipment::class, 'delivery_client_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(DeliverySettlement::class, 'delivery_client_id');
    }

    /** Create a client. Returns the plain API key too, which you can only see at this moment. */
    public static function register(string $name, ?string $webhookUrl = null, ?string $webhookSecret = null): array
    {
        $plainKey = bin2hex(random_bytes(32));

        $client = self::create([
            'name' => $name,
            'api_key_hash' => hash('sha256', $plainKey),
            'webhook_url' => $webhookUrl,
            'webhook_secret' => $webhookSecret,
            'is_active' => true,
        ]);

        return [$client, $plainKey];
    }

    public static function findByKey(string $plainKey): ?self
    {
        return self::where('api_key_hash', hash('sha256', $plainKey))->where('is_active', true)->first();
    }
}
