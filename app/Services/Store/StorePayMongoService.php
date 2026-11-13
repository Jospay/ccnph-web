<?php

namespace App\Services\Store;

use App\Models\Checkout;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class StorePayMongoService
{
    private function http(): PendingRequest
    {
        return Http::baseUrl('https://api.paymongo.com/v1')
            ->withBasicAuth((string) config('services.paymongo.secret_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(30);
    }

    /**
     * Create method + intent + attach for a checkout.
     *
     * @return array{id: string, redirect_url: ?string, qr_image: ?string}
     */
    public function charge(
        Checkout $checkout,
        PaymentMethod $method,
        User $user,
        UserAddress $address,
        string $returnUrl,
    ): array {
        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: ($user->name ?? 'User');
        // order total + online processing fee (payments.fee, in cents) is what PayMongo charges
        $feeCents = (int) ($checkout->payment?->fee ?? 0);
        $amountCents = (int) round(((float) $checkout->grand_total) * 100) + $feeCents;

        // 1. Payment method
        $pm = $this->http()->post('/payment_methods', [
            'data' => ['attributes' => [
                'type' => $method->gateway_type,
                'billing' => [
                    'name' => $name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => [
                        'line1' => trim(($address->unit_bldg_house ?? '').' '.($address->street ?? '')) ?: 'N/A',
                        'city' => $address->city,
                        'state' => $address->province,
                        'postal_code' => $address->postal_code,
                        'country' => 'PH',
                    ],
                ],
            ]],
        ])->throw()->json();

        $methodId = data_get($pm, 'data.id')
            ?? throw new RuntimeException('PayMongo: payment method not created.');

        // 2. Payment intent
        $intent = $this->http()->post('/payment_intents', [
            'data' => ['attributes' => [
                'amount' => $amountCents,
                'currency' => 'PHP',
                'payment_method_allowed' => [$method->gateway_type],
                'capture_type' => 'automatic',
                'description' => Str::limit("CCNPH Store Order {$checkout->checkout_number} - {$name}", 255),
                'statement_descriptor' => 'FCCNPH',
                'metadata' => ['checkout_id' => (string) $checkout->id],
            ]],
        ])->throw()->json();

        $intentId = data_get($intent, 'data.id')
            ?? throw new RuntimeException('PayMongo: payment intent not created.');
        $clientKey = data_get($intent, 'data.attributes.client_key');

        // 3. Attach
        $attached = $this->http()->post("/payment_intents/{$intentId}/attach", [
            'data' => ['attributes' => [
                'payment_method' => $methodId,
                'client_key' => $clientKey,
                'return_url' => $returnUrl,
            ]],
        ])->throw()->json();

        return [
            'id' => $intentId,
            'redirect_url' => $this->extractRedirect($attached),
            'qr_image' => $this->extractQr($attached),
        ];
    }

    /**
     * Ask PayMongo directly for the intent state. This is what lets the status
     * poll confirm a payment even when the webhook can't reach the server
     * (local development).
     *
     * @return array{status: ?string, failed: bool, redirect_url: ?string, qr_image: ?string, payment_id: ?string}
     */
    public function retrieve(string $intentId): array
    {
        $res = $this->http()->get("/payment_intents/{$intentId}")->throw()->json();

        $status = data_get($res, 'data.attributes.status');
        $lastError = data_get($res, 'data.attributes.last_payment_error');

        return [
            'status' => $status,
            // PayMongo drops back to awaiting_payment_method after a failed/expired attempt
            'failed' => $status === 'awaiting_payment_method' && ! empty($lastError),
            'redirect_url' => $this->extractRedirect($res),
            'qr_image' => $this->extractQr($res),
            // same id the webhook stores in payments.gateway_payment_id (needed for refunds)
            'payment_id' => data_get($res, 'data.attributes.payments.0.id'),
        ];
    }

    private function extractRedirect(array $res): ?string
    {
        return data_get($res, 'data.attributes.next_action.redirect.url');
    }

    private function extractQr(array $res): ?string
    {
        return data_get($res, 'data.attributes.next_action.code.image_url');
    }
}
