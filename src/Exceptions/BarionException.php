<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Exceptions;

use RuntimeException;

class BarionException extends RuntimeException
{
    public static function invalidEnvironment(mixed $environment): self
    {
        return new self(sprintf(
            'Invalid Barion environment [%s]. Use "test" or "production".',
            is_scalar($environment) ? (string) $environment : get_debug_type($environment),
        ));
    }

    public static function missingPosKey(string $environment): self
    {
        return new self("Missing Barion POS key for the [{$environment}] environment. Set laravel-barion.{$environment}.pos_key.");
    }

    public static function missingPayee(string $environment): self
    {
        return new self("A transaction has no Payee and no default payee is configured for the [{$environment}] environment. Set laravel-barion.{$environment}.payee.");
    }
}
