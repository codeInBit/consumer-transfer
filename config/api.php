<?php

return [
    'paystack' => [
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co/'),
        'public_key' => [
            'live' => env('PAYSTACK_PUBLIC_KEY_LIVE'),
            'test' => env('PAYSTACK_PUBLIC_KEY_TEST'),
        ],
        'secret_key' => [
            'live' => env('PAYSTACK_SECRET_KEY_LIVE'),
            'test' => env('PAYSTACK_SECRET_KEY_TEST'),
        ]
    ]
];
