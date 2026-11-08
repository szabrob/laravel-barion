<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Casts;

use BackedEnum;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * Casts secondary response values to an enum and falls back to null for
 * values Barion may add later, so a new value never breaks callback handling.
 */
final class LenientEnumCast implements Cast
{
    /**
     * @param  class-string<BackedEnum>  $enum
     */
    public function __construct(private readonly string $enum) {}

    /**
     * @param  CreationContext<Data>  $context
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): ?BackedEnum
    {
        if ($value instanceof $this->enum) {
            return $value;
        }

        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        return $this->enum::tryFrom($value);
    }
}
