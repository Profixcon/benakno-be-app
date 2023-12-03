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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'firebase' => [
        'customer_app' => [
            'service_account' => env('FIREBASE_CUSTOMER_APP_SERVICE_ACCOUNT', storage_path('app/customer_app_service_account.json')),
            'database_url' => env('FIREBASE_CUSTOMER_APP_DATABASE_URL'),
            'project_id' => env('FIREBASE_CUSTOMER_APP_PROJECT_ID'),
            'storage_bucket' => env('FIREBASE_CUSTOMER_APP_STORAGE_BUCKET'),
            'messaging_sender_id' => env('FIREBASE_CUSTOMER_APP_MESSAGING_SENDER_ID'),
            'app_id' => env('FIREBASE_CUSTOMER_APP_APP_ID'),
            'measurement_id' => env('FIREBASE_CUSTOMER_APP_MEASUREMENT_ID'),
        ],
        'partner_app' => [
            'service_account' => env('FIREBASE_PARTNER_APP_SERVICE_ACCOUNT', storage_path('app/partner_app_service_account.json')),
            'database_url' => env('FIREBASE_PARTNER_APP_DATABASE_URL'),
            'project_id' => env('FIREBASE_PARTNER_APP_PROJECT_ID'),
            'storage_bucket' => env('FIREBASE_PARTNER_APP_STORAGE_BUCKET'),
            'messaging_sender_id' => env('FIREBASE_PARTNER_APP_MESSAGING_SENDER_ID'),
            'app_id' => env('FIREBASE_PARTNER_APP_APP_ID'),
            'measurement_id' => env('FIREBASE_PARTNER_APP_MEASUREMENT_ID'),
        ],
    ],


];
