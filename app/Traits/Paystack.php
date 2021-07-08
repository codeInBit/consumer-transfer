<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Paystack
{
    public function authorizationToken()
    {
        $env = config("app.env");
        switch ($env) {
            case 'local':
                $env = 'test';
                break;
            case 'production':
                $env = 'live';
                break;
            default:
                $env = 'test';
                break;
        }

        return config("api.paystack.secret_key.$env");
    }

    public function getResponse($response)
    {
        if ($response->serverError()) {
            $data = [
                'status' => false,
                'message' => 'Sorry, the transfer cannot be completed at the moment'
            ];
            \Log::emergency('Paystack error: ' . json_encode($response->json()));

            return $data;
        }

        if (!$response->successful()) {
            $data = [
                'status' => false,
                'message' => $response->json()['message'],
                'data' => $response->json()
            ];
            \Log::info('Paystack error: ' . json_encode($response->json()));

            return $data;
        }

        return $response->json();
    }
}
