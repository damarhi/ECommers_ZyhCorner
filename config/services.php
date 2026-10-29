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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
        'sender_email' => env('BREVO_SENDER_EMAIL', env('MAIL_FROM_ADDRESS', 'damarn.h46@gmail.com')),
        'sender_name' => env('BREVO_SENDER_NAME', env('MAIL_FROM_NAME', 'Zyh Corner')),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'origin_area_id' => env('BITESHIP_ORIGIN_AREA_ID', 'ID_PLW_01'),
        'origin_postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE', '28654'),
        'origin_latitude' => env('BITESHIP_ORIGIN_LATITUDE', 0.40384),
        'origin_longitude' => env('BITESHIP_ORIGIN_LONGITUDE', 101.85588),
        'is_production' => env('BITESHIP_IS_PRODUCTION', false),
    ],

];
