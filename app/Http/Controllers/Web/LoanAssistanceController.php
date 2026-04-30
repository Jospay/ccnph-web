<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\UserType;
use App\Models\LoanSetting;
use App\Models\Loan;
use App\Models\Status;
use App\Http\Resources\LoanAssistanceResource;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LoanAssistanceController extends Controller
{
    // for write permission
    private function canMutate(): bool
    {
        return Auth::user()->user_type_id === UserType::ADMIN;
    }

    public function index(Request $request): Response
    {
        // Validate all filters
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['pending', 'active', 'rejected', 'cancelled', 'finished'])],
        ]);

        // Set defaults
        $filters = [
            'status' => $validated['status'] ?? 'pending',
        ];

        // Build and execute query
        $query = $this->buildBaseQuery($filters);
        $loans = $query->get();

        $globalSettings = LoanSetting::whereNull('user_id')->first();

        return Inertia::render('loan-assistance/Index', [
            'loans' => LoanAssistanceResource::collection($loans),
            'global_settings' => $globalSettings,
            'can_mutate' => $this->canMutate(), 
            'filters' => $filters
        ]);
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters): Builder
    {
        $query = Loan::with([
            'status:id,name',
            'user:id,name',
        ])->whereHas('status', fn($q) => $q->where('name', $filters['status']));

        return $query;
    }

    public function updateStatus(Loan $loan, Request $request)
    {
        if (!$this->canMutate()) {
            abort(403, 'Unauthorized action.');
        }

        $action = $request->input('action');

        if ($action === 'approve' && $loan->status_id === Status::PENDING) {
            DB::transaction(function () use ($loan) {

                // Refresh & Lock the loan record to prevent concurrent approval hits
                $loan->lockForUpdate();

                // Get or Create the wallet (Unique constraint in DB prevents duplicates)
                $wallet = $loan->user->wallet()->firstOrCreate(
                    ['user_id' => $loan->user_id],
                    ['balance' => 0, 'show' => true]
                );

                // Lock the wallet balance for the update
                $wallet->lockForUpdate()->get();

                // Update status and increment balance
                $loan->update(['status_id' => Status::ACTIVE]);
                $wallet->increment('balance', $loan->amount);

                // Log the transaction
                $loan->walletTransactions()->create([
                    'wallet_id'   => $wallet->id,
                    'amount'      => $loan->amount,
                    'type'        => 'deposit',
                    'description' => "Loan disbursement for Loan ID: #{$loan->id}",
                ]);
            });
        }

        if ($action === 'decline' && $loan->status_id === Status::PENDING) {
            $loan->update([
                'status_id' => Status::REJECTED,
            ]);
        }

        return back();
    }

}
