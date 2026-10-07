<?php

declare(strict_types=1);

namespace App\Services\Movider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class MoviderVerifyService
{
    private readonly ?string $key;

    private readonly ?string $secret;

    private readonly ?string $from;

    private readonly Client $client;

    public function __construct()
    {
        $this->key = config('services.movider.key');
        $this->secret = config('services.movider.secret');
        $this->from = config('services.movider.sender', 'BB88');

        $this->client = new Client([
            'base_uri' => 'https://api.movider.co/v1/',
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/x-www-form-urlencoded',
            ],
        ]);
    }

    /**
     * Standardize local phone numbers into international E.164 format.
     */
    public function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Convert PH local format "09171234567" -> "639171234567"
        if (str_starts_with($cleaned, '0')) {
            return '63'.substr($cleaned, 1);
        }

        // Convert 10-digit format "9171234567" -> "639171234567"
        if (strlen($cleaned) === 10 && str_starts_with($cleaned, '9')) {
            return '63'.$cleaned;
        }

        return $cleaned;
    }

    /**
     * Start verification (send OTP to phone).
     */
    public function startVerification(string $phone, int $codeLength = 6, string $language = 'en-us', int $expire = 300): ?array
    {
        $formattedPhone = $this->formatPhoneNumber($phone);

        try {
            $response = $this->client->post('verify', [
                'form_params' => [
                    'api_key' => $this->key,
                    'api_secret' => $this->secret,
                    'to' => $formattedPhone,
                    'code_length' => $codeLength,
                    'from' => $this->from,
                    'language' => $language,
                    'pin_expire' => $expire,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            $body = json_decode($e->getResponse()->getBody()->getContents(), true) ?? [];
            $code = $body['error']['code'] ?? null;

            if ($code === 411) {
                throw new RuntimeException('The phone number format is invalid.', 422);
            }

            throw new RuntimeException($body['error']['description'] ?? 'Failed to send verification code.', 400);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Unable to reach SMS gateway service.', 503);
        }
    }

    /**
     * Verify code entered by user.
     */
    public function acknowledge(string $requestId, string $code): ?array
    {
        try {
            $response = $this->client->post('verify/acknowledge', [
                'form_params' => [
                    'api_key' => $this->key,
                    'api_secret' => $this->secret,
                    'request_id' => $requestId,
                    'code' => $code,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return ['error' => ['code' => 500, 'description' => 'SMS Gateway unavailable.']];
        }
    }

    /**
     * Cancel verification request.
     */
    public function cancel(string $requestId): ?array
    {
        try {
            $response = $this->client->post('verify/cancel', [
                'form_params' => [
                    'api_key' => $this->key,
                    'api_secret' => $this->secret,
                    'request_id' => $requestId,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (ClientException $e) {
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return ['error' => ['code' => 500, 'description' => 'SMS Gateway unavailable.']];
        }
    }
}
