<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelBarion\LaravelBarion\Http\Controllers\CallbackController;
use LaravelBarion\LaravelBarion\LaravelBarion;

if (config('laravel-barion.callback.enabled')) {
    $path = config('laravel-barion.callback.path');
    $middleware = config('laravel-barion.callback.middleware');

    Route::match(['GET', 'POST'], is_string($path) ? $path : 'barion/callback', CallbackController::class)
        ->middleware(is_array($middleware) ? $middleware : [])
        ->name(LaravelBarion::CALLBACK_ROUTE);
}
