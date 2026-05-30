<?php

namespace App\Http\Controllers\API\Loan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Loan\ComputeLoanRequest;
use App\Http\Requests\Loan\PayLoanRequest;
use App\Http\Requests\Loan\StoreLoanRequest;
use App\Http\Resources\Api\Loan\ApiLoanResource;
use App\Models\Loan;
use App\Models\LoanSetting;
use App\Services\Loan\LoanApplicationService;
use App\Services\Loan\LoanPaymentService;
use App\Services\Loan\LoanService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LoanController extends Controller
{
    public function __construct(protected LoanService $loanService)
    {
    }

    /**
     * List Active Loans.
     *
     * Returns all pending and active loans for the authenticated user,
     * along with the remaining loanable amount.
     *
     * @tags Loan
     *
     * @response 200 scenario="success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "amount": "5000.00",
     *       "status": "Active",
     *       "due_date": "2025-06-01"
     *     }
     *   ],
     *   "meta": {
     *     "loanable_amount": "15000.00"
     *   }
     * }
     * @response 401 scenario="unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     * @response 500 scenario="server error" {
     *   "message": "Something went wrong."
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $loans = $this->loanService->getUserLoans($user);
        $loanSetting = $user->getActiveLoanSetting();
        $amount = $this->loanService->getLoanableAmount($user);

        return ApiLoanResource::collection($loans)
            ->additional([
                'meta' => array_filter([
                    'loanable_amount' => number_format($amount, 2, '.', ''),
                    'settings' => $loanSetting instanceof LoanSetting ? [
                        'default_amount' => number_format($loanSetting->default_amount, 2, '.', ''),
                        'default_interest_rate' => number_format($loanSetting->default_interest_rate, 2, '.', ''),
                        'default_term_months' => $loanSetting->default_term_months,
                    ] : null,
                    'message' => !$loanSetting instanceof LoanSetting
                        ? 'You must fully pay your share capital before you can apply for a loan.'
                        : null,
                ]),
            ])
            ->response();
    }

    /**
     * View Loan.
     *
     * Returns the full details of a specific loan belonging to the
     * authenticated user, including schedules and payments.
     *
     * @tags Loan
     *
     * @response 200 scenario="success" {
     *   "data": {
     *     "id": 1,
     *     "amount": "10000.00",
     *     "interest_rate": "2.00%",
     *     "term_months": 3,
     *     "monthly_principal": "3333.33",
     *     "start_date": "2025-04-14",
     *     "end_date": "2025-07-14",
     *     "status": "Active"
     *   }
     * }
     * @response 403 scenario="forbidden" {
     *   "message": "This action is unauthorized."
     * }
     * @response 404 scenario="not found" {
     *   "message": "Loan not found."
     * }
     * @response 401 scenario="unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     * @response 500 scenario="server error" {
     *   "message": "Something went wrong."
     * }
     */
    public function show(Request $request, Loan $loan): ApiLoanResource
    {
        if ($loan->user_id !== $request->user()->id) {
            abort(403, 'You are not authorized to view this loan.');
        }

        $loan->load([
            'user',
            'status',
            'loanSchedules',
            'loanSchedules.payments.status',
            'loanSchedules.payments.paymentMethod',
        ]);

        return new ApiLoanResource($loan);
    }

    /**
     * Apply for Loan.
     *
     * Submits a new loan application for the authenticated user.
     * The application will be processed and requires approval.
     *
     * @tags Loan
     *
     * @bodyParam amount number required The loan amount. Example: 10000
     * @bodyParam interest_rate number required The interest rate (percentage). Example: 2
     * @bodyParam term_months integer required The duration of the loan in months. Example: 3
     * @bodyParam start_date date required The start date of the loan. Example: 2025-04-14
     *
     * @response 201 scenario="success" {
     *   "success": true,
     *   "message": "Your loan application has been submitted. Please wait for approval.",
     *   "data": {
     *     "id": 1,
     *     "amount": "10000.00",
     *     "interest_rate": "2.00%",
     *     "term_months": 3,
     *     "monthly_principal": "3333.33",
     *     "start_date": "2025-04-14",
     *     "end_date": "2025-07-14",
     *     "status": "Pending"
     *   }
     * }
     *
     * @response 422 scenario="validation or domain error" {
     *   "success": false,
     *   "message": "You already have an active loan."
     * }
     *
     * @response 401 scenario="unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 500 scenario="server error" {
     *   "message": "Something went wrong."
     * }
     */
    public function store(
        StoreLoanRequest $request,
        LoanApplicationService $service
    ): JsonResponse {
        $user = $request->user();

        try {
            $loan = $service->apply($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Your loan application has been submitted. Please wait for approval.',
                'data' => new ApiLoanResource(
                    $loan->load(['status', 'loanSchedules.status'])
                ),
            ], 201);

        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get Loanable Amount.
     *
     * Returns the remaining amount the authenticated user is eligible to borrow,
     * based on their loan setting and existing active/pending loans.
     *
     * @tags Loan
     *
     * @response 200 scenario="success" {
     *   "success": true,
     *   "data": {
     *     "loanable_amount": "15000.00"
     *   }
     * }
     * @response 401 scenario="unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     * @response 500 scenario="server error" {
     *   "message": "Something went wrong."
     * }
     */
    public function getLoanableAmount(Request $request): JsonResponse
    {
        $user = $request->user();
        $loanSetting = $user->getActiveLoanSetting();
        $amount = $this->loanService->getLoanableAmount($user);

        return response()->json([
            'success' => true,
            'data' => [
                'loanable_amount' => number_format($amount, 2, '.', ''),
                'message' => !$loanSetting instanceof LoanSetting
                    ? 'You must fully pay your share capital before you can apply for a loan.'
                    : null,
            ],
        ]);
    }

    /**
     * Compute Loan Schedule.
     *
     * Calculates a full amortization schedule for a given loan amount, term,
     * and optional start date using the configured interest rate.
     *
     * @tags Loan
     *
     * @response 200 scenario="success" {
     *   "success": true,
     *   "data": {
     *     "loan_amount": "10000.00",
     *     "term_months": 3,
     *     "interest_rate": "2.00% per month",
     *     "release_date": "2025-04-14",
     *     "total_payment": "10600.00",
     *     "schedule": [
     *       {
     *         "month": 1,
     *         "due_date": "May 14, 2025",
     *         "principal": "3333.33",
     *         "interest": "200.00",
     *         "monthly_payment": "3533.33",
     *         "ending_balance": "6666.67"
     *       },
     *       {
     *         "month": 2,
     *         "due_date": "Jun 14, 2025",
     *         "principal": "3333.33",
     *         "interest": "133.33",
     *         "monthly_payment": "3466.66",
     *         "ending_balance": "3333.34"
     *       },
     *       {
     *         "month": 3,
     *         "due_date": "Jul 14, 2025",
     *         "principal": "3333.33",
     *         "interest": "66.67",
     *         "monthly_payment": "3400.00",
     *         "ending_balance": "0.00"
     *       }
     *     ]
     *   }
     * }
     * @response 422 scenario="validation error" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "amount": ["The amount field is required."],
     *     "term": ["The term field is required."]
     *   }
     * }
     * @response 401 scenario="unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     * @response 500 scenario="server error" {
     *   "message": "Something went wrong."
     * }
     */
    public function compute(ComputeLoanRequest $request): JsonResponse
    {
        $schedule = $this->loanService->computeSchedule(
            (float) $request->amount,
            (int) $request->term,
            $request->start_date,
        );

        return response()->json(['success' => true, 'data' => $schedule]);
    }

    /**
     * Pay Loan Schedule.
     *
     * Processes a payment for a specific loan belonging to the authenticated user.
     * This will update the loan schedule and record the payment.
     *
     * @tags Loan
     *
     * @bodyParam amount number required The payment amount. Example: 3333.33
     * @bodyParam payment_date date required The date the payment was made. Example: 2025-04-15
     * @bodyParam schedule_id integer required The ID of the loan schedule being paid. Example: 1
     *
     * @response 200 scenario="success" {
     *   "success": true,
     *   "message": "Schedule fully paid successfully.",
     *   "data": {
     *     "schedule_id": 1,
     *     "amount_paid": "3333.33",
     *     "remaining_balance": "6666.67",
     *     "status": "Paid"
     *   }
     * }
     *
     * @response 403 scenario="forbidden" {
     *   "message": "You are not authorized to pay this loan."
     * }
     *
     * @response 404 scenario="not found" {
     *   "message": "Loan or schedule not found."
     * }
     *
     * @response 422 scenario="validation or domain error" {
     *   "message": "Invalid payment amount."
     * }
     *
     * @response 401 scenario="unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 500 scenario="server error" {
     *   "message": "Something went wrong."
     * }
     */
    public function pay(
        PayLoanRequest $request,
        Loan $loan,
        LoanPaymentService $service
    ): JsonResponse {
        abort_if(
            $loan->user_id !== $request->user()->id,
            403,
            'Unauthorized'
        );

        $result = $service->pay($loan, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment initiated',
            'data' => $result,
        ]);
    }
}
