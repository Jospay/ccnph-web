<?php

namespace App\Http\Controllers\API\ShareCapital;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShareCapital\ApplyShareCapitalRequest;
use App\Http\Requests\ShareCapital\PayShareCapitalRequest;
use App\Http\Resources\Api\ShareCapital\ApiShareCapitalResource;
use App\Http\Resources\Api\ShareCapital\ApiShareCapitalScheduleResource;
use App\Models\MemberShareCapital;
use App\Models\ShareCapitalSchedule;
use App\Models\ShareCapitalSetting;
use App\Services\ShareCapital\ShareCapitalService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class ShareCapitalController extends Controller
{
    public function __construct(protected ShareCapitalService $service)
    {
    }

    public function settings(): JsonResponse
    {
        $setting = ShareCapitalSetting::getLatest();

        return response()->json([
            'data' => $setting ? [
                'required_amount' => number_format($setting->required_amount / 100, 2),
                'allowed_term_months' => $setting->allowed_term_months,
            ] : null,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $shareCapital = QueryBuilder::for(
            MemberShareCapital::where('user_id', $request->user()->id)
        )
            ->allowedIncludes(
                AllowedInclude::relationship('schedules'),
                AllowedInclude::relationship('schedules.payments'),
            )
            ->first();

        if (!$shareCapital) {
            return response()->json([
                'data' => null,
            ]);
        }

        return (new ApiShareCapitalResource($shareCapital))
            ->response();
    }

    public function apply(ApplyShareCapitalRequest $request): JsonResponse
    {
        try {
            $shareCapital = $this->service->apply($request->user(), $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Share capital application submitted successfully.',
                'data' => new ApiShareCapitalResource(
                    $shareCapital->load(['status', 'schedules.status'])
                ),
            ], 201);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function pay(
    PayShareCapitalRequest $request,
    ShareCapitalSchedule $schedule
): JsonResponse {
    try {

        $result = $this->service->pay(
            user: $request->user(),
            schedule: $schedule,
            data: $request->validated()
        );

        $schedule->load([
            'status',
            'payments.status',
            'payments.paymentMethod',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment initiated.',
            'data' => [
                'schedule' => new ApiShareCapitalScheduleResource($schedule),
                'payment' => $result['payment'],
            ],
            'next_action' => $result['next_action'] ?? null,
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], $e->getCode() === 403 ? 403 : 422);
    }
}
}
