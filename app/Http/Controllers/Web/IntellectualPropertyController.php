<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntellectualProperty\UpdateStatusRequest;
use App\Http\Resources\IntellectualPropertyDetailResource;
use App\Http\Resources\IntellectualPropertyResource;
use App\Models\IntellectualProperty;
use App\Models\Status;
use App\Models\UserType;
use App\Notifications\GeneralNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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
            'filters' => $filters,
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
            'conversation',
        ])->whereHas('status', fn ($q) => $q->where('name', $filters['status']));

        if (! empty($filters['creation'])) {
            $query->where('creation_type', $filters['creation']);
        }

        if (! empty($filters['form'])) {
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
            'conversation',
        ]);

        if (
            $property->form_type === 'payment' &&
            ! in_array($property->status_id, [
                Status::PENDING,
                Status::REJECTED,
            ], true)
        ) {
            $property->loadMissing([
                'schedules.status:id,name',
            ]);
        }

        return IntellectualPropertyDetailResource::make($property);
    }

    // public function updateStatus(UpdateStatusRequest $request, IntellectualProperty $property)
    // {
    //     $validated = $request->validated();

    //     if ($validated['action'] === 'approve' && $property->status_id === Status::PENDING) {
    //         if ($property->form_type === 'payment') {
    //             $property->update([
    //                 'status_id' => Status::WAITING_FOR_PAYMENT,
    //             ]);

    //             $amountInCents = (int) round($validated['amount'] * 100);
    //             $property->setting()->updateOrCreate(
    //                 [
    //                     'intellectual_property_id' => $property->id,
    //                 ],
    //                 [
    //                     'amount' => $amountInCents,
    //                     'allowed_term_months' => $validated['allowed_term_months'],
    //                 ]
    //             );

    //         } elseif ($property->form_type === 'grant') {
    //             $property->update([
    //                 'status_id' => Status::REGISTERED,
    //             ]);
    //         }
    //     }

    //     if ($validated['action'] === 'decline' && $property->status_id === Status::PENDING) {
    //         $property->update([
    //             'status_id' => Status::REJECTED,
    //         ]);
    //     }

    //     return back();
    // }

    public function updateStatus(UpdateStatusRequest $request, IntellectualProperty $property)
    {
        $validated = $request->validated();

        if ($validated['action'] === 'approve' && $property->status_id === Status::PENDING) {
            if ($property->form_type === 'payment') {
                $property->update([
                    'status_id' => Status::WAITING_FOR_PAYMENT,
                ]);

                $amountInCents = (int) round($validated['amount'] * 100);
                $property->setting()->updateOrCreate(
                    [
                        'intellectual_property_id' => $property->id,
                    ],
                    [
                        'amount' => $amountInCents,
                        'allowed_term_months' => $validated['allowed_term_months'],
                    ]
                );

                // Send general notification for payment required
                $property->user?->notify(new GeneralNotification(
                    type: 'ip_approved_payment_required',
                    title: 'Application Approved',
                    body: "Your Intellectual Property application ({$property->title}) was approved and is waiting for payment.",
                    actionType: 'MAKE_PAYMENT',
                    route: "/(intellectual)/details?id={$property->id}",
                    extraData: [
                        'property_id' => $property->id,
                        'form_type' => $property->form_type,
                        'status' => 'waiting_for_payment',
                    ]
                ));

            } elseif ($property->form_type === 'grant') {
                $property->update([
                    'status_id' => Status::REGISTERED,
                ]);

                // Send general notification for grant registration
                $property->user?->notify(new GeneralNotification(
                    type: 'ip_registered',
                    title: 'Application Registered',
                    body: "Your Intellectual Property application ({$property->title}) has been successfully registered.",
                    actionType: 'VIEW_PROPERTY',
                    route: "/(intellectual)/details?id={$property->id}",
                    extraData: [
                        'property_id' => $property->id,
                        'form_type' => $property->form_type,
                        'status' => 'registered',
                    ]
                ));
            }
        }

        if ($validated['action'] === 'decline' && $property->status_id === Status::PENDING) {
            $property->update([
                'status_id' => Status::REJECTED,
            ]);

            // Send general notification for declined status
            $property->user?->notify(new GeneralNotification(
                type: 'ip_declined',
                title: 'Application Declined',
                body: "Your Intellectual Property application ({$property->title}) was declined by the admin.",
                actionType: 'VIEW_PROPERTY',
                route: "/(intellectual)/details?id={$property->id}",
                extraData: [
                    'property_id' => $property->id, // Fixed: changed from $property->title to $property->id
                    'status' => 'rejected',
                ]
            ));
        }

        return back();
    }
}
