<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | "test" talks to https://api.test.barion.com, "production" talks to
    | https://api.barion.com. Every environment has its own shop and its own
    | POS key: the key is always taken from the matching section below, so a
    | test key can never be sent to the live API (or vice versa).
    |
    */

    'environment' => env('BARION_ENVIRONMENT', 'test'),

    'test' => [
        'pos_key' => env('BARION_TEST_POS_KEY'),
        'payee' => env('BARION_TEST_PAYEE'),
    ],

    'production' => [
        'pos_key' => env('BARION_POS_KEY'),
        'payee' => env('BARION_PAYEE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Defaults
    |--------------------------------------------------------------------------
    |
    | Values used by Payment/Start when the request DTO leaves them empty.
    | When "redirect_url" is null the request must define its own RedirectUrl.
    |
    */

    'redirect_url' => env('BARION_REDIRECT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Callback
    |--------------------------------------------------------------------------
    |
    | Barion only sends the paymentId to the callback URL, unsigned. The
    | package route always queries the v4 PaymentState API and dispatches
    | events with that verified state only. When enabled, the route URL is
    | used as the default CallbackUrl of every started payment.
    |
    */

    'callback' => [
        'enabled' => true,
        'path' => 'barion/callback',
        'middleware' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    |
    | Retries are safe: every POST call carries an Idempotency-Key header and
    | PaymentState is a read-only GET request.
    |
    */

    'http' => [
        'timeout' => 30,
        'connect_timeout' => 10,
        'retry' => [
            'times' => 2,
            'sleep' => 200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Barion Pixel
    |--------------------------------------------------------------------------
    |
    | The <x-laravel-barion::pixel /> component renders the Barion Pixel base
    | code when an id (BP-XXXXXXXXXX-XX) is configured.
    |
    */

    'pixel' => [
        'id' => env('BARION_PIXEL_ID'),
    ],

];
