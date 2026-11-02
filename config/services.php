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

    'firebase' => [
        'push_enabled' => env('FIREBASE_PUSH_ENABLED', true),
        'web' => [
            'api_key' => env('FIREBASE_API_KEY', 'AIzaSyAJ3NyreNoyb-qJqhkcvAs5Z1axSPJFnF8'),
            'auth_domain' => env('FIREBASE_AUTH_DOMAIN', 'bazaramal-7a9bd.firebaseapp.com'),
            'project_id' => env('FIREBASE_PROJECT_ID', 'bazaramal-7a9bd'),
            'storage_bucket' => env('FIREBASE_STORAGE_BUCKET', 'bazaramal-7a9bd.firebasestorage.app'),
            'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '47942621522'),
            'app_id' => env('FIREBASE_APP_ID', '1:47942621522:web:2c5176ce9b45a2c427428b'),
            'vapid_key' => env('FIREBASE_VAPID_KEY', 'BM2pCVPxFfFrfhdHlkAFg2Lrw0oThUxCgRjE-GmmgTN36q5R4yZUY-fl2tzw0iCuTlIMjryJxmgkzIViiK_Fo1U'),
        ],
    ],

];
