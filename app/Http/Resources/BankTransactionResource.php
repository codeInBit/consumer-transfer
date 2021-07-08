<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BankTransactionResource extends JsonResource
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
            'provider' => $this->provider,
            'reference' => $this->reference,
            'transfer_code' => $this->transfer_code,
            'status' => $this->status,
            'narration' => $this->narration,
            'transfer_recipient' => new TransferRecipientResource($this->whenLoaded('transferRecipient')),
        ];
    }
}
