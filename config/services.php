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

    'barcode_lookup' => [
        'providers' => array_filter(explode(',', env('BARCODE_LOOKUP_PROVIDERS', 'open_food_facts,open_products_facts,open_beauty_facts,upcitemdb'))),
        'timeout_seconds' => (float) env('BARCODE_LOOKUP_TIMEOUT_SECONDS', 2),
        'connect_timeout_seconds' => (float) env('BARCODE_LOOKUP_CONNECT_TIMEOUT_SECONDS', 1),
        'not_found_cache_days' => (int) env('BARCODE_LOOKUP_NOT_FOUND_CACHE_DAYS', 7),
    ],

];
