<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LaravelBarion\LaravelBarion\LaravelBarion
 */
class LaravelBarion extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelBarion\LaravelBarion\LaravelBarion::class;
    }
}
