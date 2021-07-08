<?php

namespace App\Services\ThirdPartyService\Payment\Paystack;

use Illuminate\Support\Facades\Http;
use App\Traits\Paystack;

class IdentityVerification
{
    use Paystack;

    private const BASE_URL = "https://api.paystack.co/";

    public function verifyBVNMatch(
        string $bvn,
        string $accountNumber,
        string $bankCode,
        string $firstName = null,
        string $lastName = null,
        string $middleName = null
    ): array {
        $url = self::BASE_URL . "bvn/match";
        $response = Http::withToken($this->authorizationToken())->post(
            $url,
            [
                'bvn' => $bvn,
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName,
            ]
        );

        return $this->getResponse($response);
    }

    public function resolveBVN(string $bvn): array
    {
        $url = self::BASE_URL . "bank/resolve_bvn/$bvn";
        $response = Http::withToken($this->authorizationToken())->get($url);

        return $this->getResponse($response);
    }

    public function resolveAccountNumber(string $accountNumber, string $bankCode): array
    {
        $url = self::BASE_URL . "bank/resolve/?account_number=$accountNumber&bank_code=$bankCode";
        $response = Http::withToken($this->authorizationToken())->get($url);

        return $this->getResponse($response);
    }

    public function resolveCardPin(string $bin): array
    {
        $url = self::BASE_URL . "decision/bin/$bin";
        $response = Http::withToken($this->authorizationToken())->get($url);

        return $this->getResponse($response);
    }
}
