<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Exceptions;

use Throwable;

/**
 * Barion answered successfully, but the response could not be mapped to the
 * expected DTO (missing or unexpected field). The raw body is kept for debugging.
 */
final class BarionResponseException extends BarionException
{
    /**
     * @param  array<array-key, mixed>  $body
     */
    public function __construct(
        string $message,
        public readonly string $dataClass,
        public readonly array $body = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @param  class-string  $dataClass
     * @param  array<array-key, mixed>  $body
     */
    public static function unmappable(string $endpoint, string $dataClass, array $body, Throwable $previous): self
    {
        return new self(
            sprintf('Unexpected Barion API response from [%s]; it cannot be mapped to [%s]: %s', $endpoint, class_basename($dataClass), $previous->getMessage()),
            $dataClass,
            $body,
            $previous,
        );
    }
}
