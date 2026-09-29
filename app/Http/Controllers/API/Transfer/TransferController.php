<?php

namespace App\Http\Controllers\API\Transfer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\TransferRequest;
use App\Http\Resources\Api\Transfer\ApiBatchTransferResource;
use App\Http\Resources\Api\Wallet\ApiWalletResource;
use App\Services\Transfer\TransferService;
use App\Services\Wallet\WalletService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TransferController extends Controller
{
    public function __construct(
        protected TransferService $transferService,
        protected WalletService $walletService,
    ) {}

    public function config(): JsonResponse
    {
        $fee = $this->transferService->getFeeConfig();

        return response()->json([
            'data' => [
                'min_transfer' => $this->transferService->getMinTransfer(),
                'fee' => [
                    'type' => $fee?->type ?? 'PHP',
                    'transfer_fee' => (float) ($fee?->transfer_fee ?? 0),
                ],
                'channels' => $this->transferService->getActiveChannels()->map(fn ($c) => [
                    'id' => $c->code,
                    'name' => $c->name,
                    'category' => $c->category,
                ])->values(),
            ],
        ]);
    }

    public function store(TransferRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $wallet = $this->walletService->getUserWallet($user);

        if ($wallet->isTampered()) {
            return response()->json([
                'success' => false,
                'is_tampered' => true,
                'message' => 'Your wallet balance integrity check failed. Transactions are restricted.',
            ], 422);
        }

        if ($verificationError = $this->verifyTransferAuthorization($request, $validated)) {
            return $verificationError;
        }

        try {
            $batchTransfer = $this->transferService->transfer($user, $validated);
            $wallet = $this->walletService->getUserWallet($user->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Transfer submitted successfully.',
                'data' => new ApiBatchTransferResource($batchTransfer),
                'wallet' => new ApiWalletResource($wallet),
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function status(Request $request, string $reference): JsonResponse
    {
        $batchTransfer = $request->user()->wallet
            ->batchTransfers()
            ->where('reference_number', $reference)
            ->firstOrFail();

        return response()->json([
            'data' => new ApiBatchTransferResource($batchTransfer),
        ]);
    }

    private function verifyTransferAuthorization(Request $request, array $validated): ?JsonResponse
    {
        $user = $request->user();

        if ($validated['verification_method'] === 'password') {
            if (! Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The password you entered is incorrect.',
                ], 422);
            }

            return null;
        }

        // verification_method === 'biometric'
        $device = $user->authDevices()
            ->where('device_id', $validated['device_id'])
            ->where('biometric_enabled', true)
            ->first();

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Quick and Secure Login is not enabled on this device.',
            ], 422);
        }

        return null;
    }
}
