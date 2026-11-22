<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'payment' => [
        'driver' => env('PAYMENT_GATEWAY', 'zarinpal'),
    ],

    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
        'sandbox' => env('ZARINPAL_SANDBOX', true),
    ],

    'farazsms' => [
        'base_url' => env('FARAZSMS_BASE_URL', 'https://api.iranpayamak.com'),
        'api_key' => env('FARAZSMS_API_KEY'),
        'pattern_code' => env('FARAZSMS_PATTERN_CODE'),
        'otp_param' => env('FARAZSMS_OTP_PARAM', 'code'),
        'line_number' => env('FARAZSMS_LINE_NUMBER', '90008361'),
        // Local Windows PHP often needs an explicit CA bundle for Guzzle/cURL.
        'ca_bundle' => env('FARAZSMS_CA_BUNDLE', ini_get('curl.cainfo') ?: ini_get('openssl.cafile') ?: ''),
    ],

];
