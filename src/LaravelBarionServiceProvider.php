<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class LaravelBarionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-barion.php', 'laravel-barion');

        $this->app->singleton(LaravelBarion::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for(LaravelBarion::CALLBACK_RATE_LIMITER, function (Request $request): Limit {
            $perMinute = config('laravel-barion.callback.rate_limit');

            if (! is_numeric($perMinute)) {
                return Limit::none();
            }

            // Keyed by IP and paymentId: Barion sends every callback from a few
            // IPs, so a per-IP key alone would throttle busy shops. Dashes and
            // case are normalized, so the variants of one id share a counter.
            $paymentId = $request->input('paymentId', $request->input('PaymentId'));
            $paymentId = is_string($paymentId) ? strtolower(str_replace('-', '', $paymentId)) : '';

            return Limit::perMinute((int) $perMinute)->by($request->ip().'|'.$paymentId);
        });

        $this->loadRoutesFrom(__DIR__.'/../routes/laravel-barion.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-barion');

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'laravel-barion');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-barion.php' => config_path('laravel-barion.php'),
        ], ['laravel-barion', 'laravel-barion-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-barion'),
        ], ['laravel-barion', 'laravel-barion-views']);
    }
}
