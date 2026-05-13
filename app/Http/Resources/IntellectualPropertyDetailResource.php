<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Status;

class IntellectualPropertyDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status_name' => $this->status->name,
            'user_name' => $this->user->name ?? 'N/A',
            'creation_type' => $this->creation_type,
            'form_type' => $this->form_type,
            'amount' => (float) $this->amount ?? null,
            'term_months' => (int) $this->term_months ?? null,
            'title' => $this->title,
            'description' => $this->description,
            'applicability' => $this->applicability,

            'claims' => $this->whenLoaded('claims', function () {
                return $this->claims->map(fn($claim) => [
                    'id' => $claim->id,
                    'description' => $claim->description,
                ]);
            }),

            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(fn($doc) => [
                    'id' => $doc->id,
                    'attachment' => $doc->attachment ? Storage::url($doc->attachment) : null,
                ]);
            }),

            'schedules' => $this->when(
                $this->form_type === 'payment' &&
                $this->status_id !== Status::PENDING,
                fn () => $this->schedules->map(fn ($schedule) => [
                    'id' => $schedule->id,
                    'installment_no' => $schedule->installment_no,
                    'amount' => $schedule->amount,
                    'due_date' => $schedule->due_date?->format('M d, Y'),
                    'status_name' => $schedule->status->name,
                ])
            ),
        ];
    }
}
