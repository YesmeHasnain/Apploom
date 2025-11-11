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
        'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URL', 'http://127.0.0.1:8000/auth/callback/github'),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL', 'http://127.0.0.1:8000/auth/callback/google'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URL', 'http://127.0.0.1:8000/auth/callback/facebook'),
    ],
    'genai' => [
        'provider'       => env('AI_PROVIDER', 'google'),
        'google_api_key' => env('GOOGLE_AI_API_KEY'),
        'google_model'   => env('GOOGLE_AI_MODEL', 'gemini-1.5-flash'),
    ],
    'gemini' => [
        'key'      => env('GOOGLE_GENAI_API_KEY'),
        'model'    => env('GOOGLE_GENAI_MODEL', 'gemini-1.5-flash-latest'),
        'endpoint' => env('GOOGLE_GENAI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta'),
    ],
    


];
