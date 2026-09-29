<?php

namespace App\Services\Transfer;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymongoTransferService
{
    protected string $baseUrl;

    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('paymongo.base_url'), '/');
        $this->secretKey = (string) config('paymongo.secret_key');
    }

    /**
     * Fetch (and cache) the list of receiving institutions for a rail.
     * Only successful responses are cached — a failed call is never
     * persisted, so a transient error can't lock in an empty result
     * for hours.
     */
    public function getReceivingInstitutions(string $provider = 'instapay'): array
    {
        $cacheKey = "paymongo:receiving_institutions:{$provider}";

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->get("{$this->baseUrl}/v1/wallets/receiving_institutions", [
                'provider' => $provider,
            ]);

        if (! $response->successful()) {
            Log::warning('PayMongo receiving_institutions request failed', [
                'provider' => $provider,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [];
        }

        $data = $response->json('data', []);

        if (empty($data)) {
            Log::warning('PayMongo receiving_institutions returned no data', [
                'provider' => $provider,
                'raw' => $response->json(),
            ]);

            return [];
        }

        Cache::put($cacheKey, $data, now()->addHours(6));

        return $data;
    }

    /**
     * Resolve a receiving institution's provider_code (used as destination BIC)
     * by fuzzy-matching its name.
     */
    public function resolveBic(string $searchTerm, string $provider = 'instapay'): ?string
    {
        $institutions = $this->getReceivingInstitutions($provider);

        foreach ($institutions as $institution) {
            $attrs = $institution['attributes'] ?? $institution;
            $name = $attrs['name'] ?? '';

            if ($name !== '' && Str::contains(Str::lower($name), Str::lower($searchTerm))) {
                return $attrs['provider_code'] ?? $attrs['bic'] ?? null;
            }
        }

        Log::warning('PayMongo BIC resolution failed: no institution matched', [
            'search_term' => $searchTerm,
            'provider' => $provider,
            'available_names' => array_map(
                fn ($i) => ($i['attributes'] ?? $i)['name'] ?? null,
                $institutions,
            ),
        ]);

        return null;
    }

    /**
     * Submit a single transfer via the batch_transfers endpoint.
     */
    public function createBatchTransfer(array $transfer): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post("{$this->baseUrl}/v2/batch_transfers", [
                'transfers' => [$transfer],
            ]);

        if (! $response->successful()) {
            Log::warning('PayMongo createBatchTransfer failed', [
                'status' => $response->status(),
                'body' => $response->json(),
                'request' => $transfer,
            ]);
        }

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json(),
        ];
    }
}
