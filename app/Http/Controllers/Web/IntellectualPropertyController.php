<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use App\Models\UserType;
use App\Models\IntellectualProperty;
use App\Models\Status;
use App\Http\Resources\IntellectualPropertyResource;
use App\Http\Resources\IntellectualPropertyDetailResource;
use App\Http\Requests\IntellectualProperty\UpdateStatusRequest;
use Inertia\Inertia;

class IntellectualPropertyController extends Controller
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
            'status' => ['sometimes', 'string', Rule::in(['pending', 'registered', 'rejected', 'expired', 'waiting_for_payment'])],
            'creation' => ['sometimes', 'string', Rule::in(['business_idea', 'invention'])],
            'form' => ['sometimes', 'string', Rule::in(['payment', 'grant'])],
        ]);

        // Set defaults
        $filters = [
            'status' => $validated['status'] ?? 'pending',
            'creation' => $validated['creation'] ?? null,
            'form' => $validated['form'] ?? null,
        ];

        // Build and execute query
        $query = $this->buildBaseQuery($filters);
        $intellectualProperties = $query->get();

        return Inertia::render('intellectual-property/Index', [
            'intellectual_properties' => IntellectualPropertyResource::collection($intellectualProperties),
            'can_mutate' => $this->canMutate(), 
            'filters' => $filters
        ]);
    }

    /**
     * Creates the base query with all "WHERE" conditions.
     */
    private function buildBaseQuery(array $filters): Builder
    {
        $query = IntellectualProperty::with([
            'status:id,name',
            'user:id,name',
        ])->whereHas('status', fn($q) => $q->where('name', $filters['status']));

        if (!empty($filters['creation'])) {
            $query->where('creation_type', $filters['creation']);
        }

        if (!empty($filters['form'])) {
            $query->where('form_type', $filters['form']);
        }

        return $query;
    }

    public function show(IntellectualProperty $property)
    {
        $property->loadMissing([
            'status:id,name',
            'user:id,name',
            'claims',
            'documents',
        ]);

        return IntellectualPropertyDetailResource::make($property);
    }

    public function updateStatus(UpdateStatusRequest $request, IntellectualProperty $property,)
    {
        // Authorization and Validation
        $action = $request->validated('action');
        $amount = $request->validated('amount');

        if ($action === 'approve' && $property->status_id === Status::PENDING) {
            if ($property->form_type === 'payment') {
                $property->update([
                    'amount' => $amount,
                    'status_id' => Status::WAITING_FOR_PAYMENT,
                ]);
            } elseif ($property->form_type === 'grant') {
                $property->update([
                    'status_id' => Status::REGISTERED,
                ]);
            }
        }

        if ($action === 'decline' && $property->status_id === Status::PENDING) {
            $property->update([
                'status_id' => Status::REJECTED,
            ]);
        }

        return back();
    }
}
