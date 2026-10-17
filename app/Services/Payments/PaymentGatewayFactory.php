<?php

namespace App\Services\Payments;

use App\Models\PaymentMethod;
use App\Services\Payments\Gateways\PayMongoService;

class PaymentGatewayFactory
{
    /**
     * Create a new class instance.
     */
    public static function make(string $gateway)
    {
        return match ($gateway) {
            'paymongo' => app(PayMongoService::class),

            default => throw new \InvalidArgumentException("Unsupported gateway: {$gateway}"),
        };
    }

    public static function resolveGateway(PaymentMethod $method): string
    {
        return match ($method->id) {
            PaymentMethod::CASH => 'cash',
            PaymentMethod::WALLET => 'wallet',
            PaymentMethod::POINT => 'point',
            PaymentMethod::CASH_ON_DELIVERY => 'cod',

            PaymentMethod::CARD, PaymentMethod::QR_CODE,
            PaymentMethod::MAYA, PaymentMethod::BILLEASE,
            PaymentMethod::GRAB_PAY, PaymentMethod::DOB => 'paymongo',

            default => throw new \InvalidArgumentException(
                "No gateway for payment method {$method->id}"
            ),
        };
    }
}
