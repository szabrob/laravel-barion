<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Tests;

use LaravelBarion\LaravelBarion\LaravelBarionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            LaravelBarionServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('laravel-barion.environment', 'test');
        $app['config']->set('laravel-barion.test.pos_key', 'test-pos-key');
        $app['config']->set('laravel-barion.test.payee', 'test-payee@example.com');
        $app['config']->set('laravel-barion.production.pos_key', 'live-pos-key');
        $app['config']->set('laravel-barion.production.payee', 'live-payee@example.com');
        $app['config']->set('laravel-barion.http.retry.sleep', 0);
    }
}
