<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Concerns;

/**
 * Barion sends null for absent values (e.g. "Items": null). Dropping them
 * before the pipeline lets the property defaults apply, so response DTOs can
 * be created from raw Barion JSON as well.
 */
trait IgnoresNullInput
{
    /**
     * @param  array<array-key, mixed>  $properties
     * @return array<array-key, mixed>
     */
    public static function prepareForPipeline(array $properties): array
    {
        return array_filter($properties, fn (mixed $value): bool => $value !== null);
    }
}
