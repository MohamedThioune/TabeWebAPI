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
        'region' => env('AWS_DEFAULT_REGION', 'us-east-2'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'phone' => env('TWILIO_PHONE_NUMBER', '+16504803258'),
        'whatsapp' => env('TWILIO_WHATSAPP_NUMBER'),
    ],

    'paydunya' => [
        'url' => env('PAYDUNYA_BASE_URL') . "/api/" . env('PAYDUNYA_VERSION', "v1"),
        'urlSandBox' => env('PAYDUNYA_BASE_URL') . "/sandbox-api/" . env('PAYDUNYA_VERSION', "v1"),
        'name' => env('PAYDUNYA_COMPANY_NAME'),
        'masterKey' => env('PAYDUNYA_MASTERKEY'),
        'publicKey' => env('PAYDUNYA_PUBLIC_KEY'),
        'privateKey' => env('PAYDUNYA_PRIVATE_KEY'),
        'token' => env('PAYDUNYA_TOKEN'),
        'mode' => env('PAYDUNYA_PAYMENT_MODE', 'test')
    ]

];
