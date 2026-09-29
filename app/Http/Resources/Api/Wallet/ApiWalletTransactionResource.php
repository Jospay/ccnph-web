<?php

namespace App\Http\Resources\Api\Wallet;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiWalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'amount' => number_format($this->amount, 2, '.', ''),
            'transfer_fee' => number_format($this->transfer_fee ?? 0, 2, '.', ''),
            'type' => $this->type,
            'from_name' => $this->from_name,
            'from_account_number' => $this->from_account_number,
            'to_account_name' => $this->to_account_name,
            'to_account_number' => $this->to_account_number,
            'to_provider' => $this->to_provider,
            'description' => $this->description,
            'reference_id' => $this->reference_id,
            'reference_type' => $this->reference_type,
            'reference_number' => $this->reference_number,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
