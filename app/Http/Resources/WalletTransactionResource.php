<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'amount' => $this->amount,
            'prev_balance' => $this->prev_balance,
            'current_balance' => $this->current_balance,
            'reference' => $this->reference,
            'trx_type' => $this->trx_type,
            'purpose' => $this->purpose,
            'transaction_date' => $this->transaction_date,
            'bank_transaction' => new BankTransactionResource($this->whenLoaded('bankTransaction')),
        ];
    }
}
