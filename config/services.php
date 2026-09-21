<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'vietqr' => [
    'bank_id' => env('VIETQR_BANK_ID', '970422'),
    'account_no' => env('VIETQR_ACCOUNT_NO'),
    'account_name' => env('VIETQR_ACCOUNT_NAME', 'LE MINH TAN'),
],
'momo' => [
    'enabled' => env('MOMO_ENABLED', false),

    'partner_code' => env('MOMO_PARTNER_CODE'),
    'access_key' => env('MOMO_ACCESS_KEY'),
    'secret_key' => env('MOMO_SECRET_KEY'),

    'endpoint' => env(
        'MOMO_ENDPOINT',
        'https://test-payment.momo.vn/v2/gateway/api/create'
    ),

    'redirect_url' => env('MOMO_REDIRECT_URL'),
    'ipn_url' => env('MOMO_IPN_URL'),
],

'zalopay' => [
    'enabled' => env('ZALOPAY_ENABLED', false),

    'app_id' => env('ZALOPAY_APP_ID'),
    'key1' => env('ZALOPAY_KEY1'),
    'key2' => env('ZALOPAY_KEY2'),

    'create_endpoint' => env(
        'ZALOPAY_CREATE_ENDPOINT',
        'https://sb-openapi.zalopay.vn/v2/create'
    ),

    'query_endpoint' => env(
        'ZALOPAY_QUERY_ENDPOINT',
        'https://sb-openapi.zalopay.vn/v2/query'
    ),

    'callback_url' => env('ZALOPAY_CALLBACK_URL'),
    'redirect_url' => env('ZALOPAY_REDIRECT_URL'),
],
'stripe' => [
    'enabled' => env('STRIPE_ENABLED', false),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'success_url' => env('STRIPE_SUCCESS_URL'),
    'cancel_url' => env('STRIPE_CANCEL_URL'),
],

'paypal' => [
    'enabled' => env('PAYPAL_ENABLED', true),
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'base_url' => env(
        'PAYPAL_BASE_URL',
        'https://api-m.sandbox.paypal.com'
    ),
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
'vnd_per_usd' => (float) env('PAYPAL_VND_PER_USD', 25000),
],
];
