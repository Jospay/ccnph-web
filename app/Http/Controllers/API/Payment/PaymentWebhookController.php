<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayLog;
use App\Services\Payments\PaymentGatewayFactory;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentWebhookService $webhookService,
    ) {}

    public function __invoke(Request $request, string $gateway): JsonResponse
    {
        $service = PaymentGatewayFactory::make($gateway);

        $data = $service->parseWebhook($request->all());

        PaymentGatewayLog::create([
            'payment_id' => null,
            'gateway' => $gateway,
            'event' => 'webhook_received',
            'payload' => $request->all(),
        ]);

        if (empty($data['intent_id']) || empty($data['status'])) {
            PaymentGatewayLog::create([
                'payment_id' => null,
                'gateway' => $gateway,
                'event' => 'invalid_payload',
                'payload' => $request->all(),
            ]);

            return response()->json(['message' => 'Invalid webhook payload'], 200);
        }

        $this->webhookService->handle(
            gatewayPaymentIntentId: $data['intent_id'],
            gatewayStatus: $data['status'],
            gatewayPaymentId: $data['gateway_payment_id'],
        );

        return response()->json(['ok' => true], 200);
    }
}
