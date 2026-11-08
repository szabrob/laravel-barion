<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Casts;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * Barion returns ISO-8601 timestamps with a varying fraction precision and
 * sometimes without an offset; values without an offset are UTC.
 */
final class BarionDateTimeCast implements Cast
{
    /**
     * @param  CreationContext<Data>  $context
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): ?CarbonImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        return CarbonImmutable::parse($value, 'UTC');
    }
}
