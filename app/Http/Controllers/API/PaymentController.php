<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
class PaymentController extends Controller
{
    /**
     * Mobile will call this to check status
     */
    public function status(string $paymentIntentId): JsonResponse
    {
        $payment = Payment::where('gateway_payment_intent_id', $paymentIntentId)->first();

        if (!$payment) {
            return response()->json([
                'status' => 'not_found'
            ], 404);
        }

        return response()->json([
            'status' => $payment->status_id,
            'paid_at' => $payment->paid_at,
            'gateway_payment_intent_id' => $payment->gateway_payment_intent_id,
        ]);
    }

    /**
     * Optional fallback endpoint (NO BUSINESS LOGIC HERE)
     */
    public function success(): JsonResponse
    {
        return response()->json([
            'message' => 'Redirect received (UI only)'
        ]);
    }
}
