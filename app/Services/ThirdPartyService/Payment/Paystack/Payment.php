<?php

namespace App\Services\ThirdPartyService\Payment\Paystack;

use Illuminate\Support\Facades\Http;
use App\Traits\Paystack;

class Payment
{
    use Paystack;

    private const BASE_URL = "https://api.paystack.co/";

    public function verifyTransaction(string $transactionReference): array
    {
        $url = self::BASE_URL . "transaction/verify/$transactionReference";
        $response = Http::withToken($this->authorizationToken())->get($url);

        return $this->getResponse($response);
    }

    public function chargeAuthorization(string $authorizationCode, string $email, string $amount, $metaData = []): array
    {
        $url = self::BASE_URL . "transaction/charge_authorization";
        $response = Http::withToken($this->authorizationToken())->post(
            $url,
            [
                "authorization_code" => $authorizationCode,
                "email" => $email,
                "amount" => $amount,
                'metadata' => $metaData,
            ]
        );

        return $this->getResponse($response);
    }

    public function checkAuthorization(string $authorizationCode, string $email, string $amount): array
    {
        $url = self::BASE_URL . "transaction/check_authorization";
        $response = Http::withToken($this->authorizationToken())->post(
            $url,
            [
                "authorization_code" => $authorizationCode,
                "email" => $email,
                "amount" => $amount,
            ]
        );

        return $this->getResponse($response);
    }
}
