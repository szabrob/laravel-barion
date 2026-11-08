<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Tests;

use LaravelBarion\LaravelBarion\LaravelBarionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelBarionServiceProvider::class,
        ];
    }
}
