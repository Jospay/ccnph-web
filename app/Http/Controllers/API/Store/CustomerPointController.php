<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Points\LoadPointsRequest;
use App\Http\Resources\Api\Points\ApiPointResource;
use App\Http\Resources\Api\Points\ApiPointTransactionResource;
use App\Services\Points\PointService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerPointController extends Controller
{
    public function __construct(protected PointService $pointService) {}

    /**
     * GET store/points — balance, visibility, integrity status.
     */
    public function index(Request $request): JsonResponse
    {
        $point = $this->pointService->getUserPoint($request->user());

        return (new ApiPointResource($point))->response();
    }

    /**
     * POST store/points/visibility — toggle show/hide of the balance.
     */
    public function toggleVisibility(Request $request): JsonResponse
    {
        $point = $this->pointService->getUserPoint($request->user());

        $point->show = ! $point->show;
        $point->save();

        return (new ApiPointResource($point))->response();
    }

    /**
     * GET store/points/transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $point = $this->pointService->getUserPoint($request->user());

        return ApiPointTransactionResource::collection(
            $this->pointService->getPointTransactions($point)
        )->response();
    }

    /**
     * GET store/points/config — dynamic minimum + fee.
     */
    public function config(): JsonResponse
    {
        $fee = $this->pointService->getFeeConfig();

        return response()->json([
            'data' => [
                'min_load' => $this->pointService->getMinLoad(),
                'fee' => [
                    'type' => $fee?->type ?? 'PHP',
                    'fee' => (float) ($fee?->transfer_fee ?? 0),
                ],
            ],
        ]);
    }

    /**
     * POST store/points/load
     */
    public function load(LoadPointsRequest $request): JsonResponse
    {
        $user = $request->user();

        $point = $this->pointService->getUserPoint($user);

        if ($point->isTampered()) {
            return response()->json([
                'success' => false,
                'is_tampered' => true,
                'message' => 'Your points balance integrity check failed. Transactions are restricted.',
            ], 422);
        }

        try {
            $result = $this->pointService->load($user, $request->validated());

            $point = $this->pointService->getUserPoint($user->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Points load initiated successfully.',
                'data' => new ApiPointResource($point),

                // Frontend needs gateway_payment_intent_id to poll status
                'payment' => $result['payment'],

                'next_action' => $result['next_action'],
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
