<?php

return [

    'sao_miguel' => [
        'base_url'      => env('SAO_MIGUEL_API_URL'),
        'access_key'    => env('SAO_MIGUEL_ACCESS_KEY'),
        'customer_cnpj' => env('SAO_MIGUEL_CUSTOMER_CNPJ'),
    ],

    'alfa' => [
        'base_url' => env('ALFA_BASE_URL', ''),
        'token' => env('ALFA_ACCESS_KEY', ''),
        'customer_cnpj' => env('ALFA_CUSTOMER_CNPJ', ''),
    ],
    'patrus' => [
        'subscription' => env('PATRUS_SUBSCRIPTION'),
        'username'     => env('PATRUS_USERNAME'),
        'password'     => env('PATRUS_PASSWORD'),
    ],
];
