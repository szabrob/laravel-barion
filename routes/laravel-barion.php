<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Szabrob\LaravelBarion\Http\Controllers\CallbackController;
use Szabrob\LaravelBarion\LaravelBarion;

if (config('laravel-barion.callback.enabled')) {
    $path = config('laravel-barion.callback.path');
    $middleware = config('laravel-barion.callback.middleware');
    $middleware = is_array($middleware) ? $middleware : [];

    if (config('laravel-barion.callback.rate_limit') !== null) {
        $middleware[] = 'throttle:'.LaravelBarion::CALLBACK_RATE_LIMITER;
    }

    Route::match(['GET', 'POST'], is_string($path) ? $path : 'barion/callback', CallbackController::class)
        ->middleware($middleware)
        ->name(LaravelBarion::CALLBACK_ROUTE);
}
