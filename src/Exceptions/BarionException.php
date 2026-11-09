<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Exceptions;

use RuntimeException;

class BarionException extends RuntimeException
{
    /**
     * Body fields that carry the POS key. Barion echoes the POS key in the
     * AuthData field of every error, so it would end up in logs and error
     * trackers through the exception body.
     */
    public const array SECRET_FIELDS = ['authdata', 'poskey'];

    public const string REDACTED = '[redacted]';

    /**
     * @param  array<array-key, mixed>  $body
     * @return array<array-key, mixed>
     */
    public static function redactSecrets(array $body): array
    {
        foreach ($body as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::SECRET_FIELDS, true)) {
                $body[$key] = $value === null ? null : self::REDACTED;
            } elseif (is_array($value)) {
                $body[$key] = self::redactSecrets($value);
            }
        }

        return $body;
    }

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
