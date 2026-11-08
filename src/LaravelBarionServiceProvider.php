<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion;

use Illuminate\Support\Facades\Blade;
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
