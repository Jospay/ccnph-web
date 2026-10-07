<?php

return [
    'secret_key' => env('PAYMONGO_SECRET_KEY'),
    'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com'),

    'source_account' => [
        'number' => env('PAYMONGO_SOURCE_ACCOUNT_NUMBER'),
        'name' => env('PAYMONGO_SOURCE_ACCOUNT_NAME'),
        'bic' => env('PAYMONGO_SOURCE_ACCOUNT_BIC', 'PAEYPHM2XXX'),
    ],

    'callback_url' => env('PAYMONGO_TRANSFER_CALLBACK_URL'),
    'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
];
