<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class LoanAssistanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        return [
            'id' => $this->id,
            'status_name' => $this->status->name,
            'user_name' => $this->user->name ?? 'N/A',
            'amount' => (float) $this->amount,
            'interest_rate' => (float) $this->interest_rate, 
            'term_months'   => (int) $this->term_months,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,

            // display purposes
            'interest_rate_display' => rtrim(rtrim(number_format($this->interest_rate * 100, 2), '0'), '.') . '%',
            'start_date_display'    => $startDate->format('M d, Y'),
            'end_date_display'      => $endDate->format('M d, Y'),
        ];
    }
}
