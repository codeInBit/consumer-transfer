<?php

namespace App\Services\ThirdPartyService\Payment\Paystack;

use Illuminate\Support\Facades\Http;
use App\Traits\Paystack;

class Transfer
{
    use Paystack;

    private const BASE_URL = "https://api.paystack.co/";

    public function createTransferRecipient(string $name, $accountNumber, $bankCode): array
    {
        $url = self::BASE_URL . "transferrecipient";
        $response = Http::withToken($this->authorizationToken())->post(
            $url,
            [
                "type" => "nuban",
                "name" => $name,
                "account_number" => $accountNumber,
                "bank_code" => $bankCode,
                "currency" => "NGN",
            ]
        );

        return $this->getResponse($response);
    }

    public function initiateSingleTransfer(
        string $amount,
        string $recipientCode,
        string $description,
        string $balance = 'balance'
    ): array {
        $url = self::BASE_URL . "transfer";
        $response = Http::withToken($this->authorizationToken())->post(
            $url,
            [
                "source" => $balance,
                "amount" => $amount,
                "recipient" => $recipientCode,
                "reason" => $description,
            ]
        );

        return $this->getResponse($response);
    }

    public function initiateBulkTransfer(array $payload): array
    {
        $url = self::BASE_URL . "transfer";
        $response = Http::withToken($this->authorizationToken())->post($url, $payload);

        return $this->getResponse($response);
    }
}
