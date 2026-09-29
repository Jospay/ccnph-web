<?php

namespace App\Http\Resources\Api\Transfer;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiBatchTransferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'status' => $this->status,
            'channel' => $this->channel,
            'destination_account_name' => $this->destination_account_name,
            'destination_account_number' => $this->destination_account_number,
            'amount' => number_format($this->amount, 2, '.', ''),
            'fee' => number_format($this->fee, 2, '.', ''),
            'total_deducted' => number_format($this->amount + $this->fee, 2, '.', ''),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
