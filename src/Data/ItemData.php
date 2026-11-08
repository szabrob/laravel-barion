<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapName(StudlyCaseMapper::class)]
final class ItemData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly float $quantity,
        public readonly string $unit,
        public readonly float $unitPrice,
        public readonly float $itemTotal,
        #[MapName('SKU')]
        public readonly ?string $sku = null,
    ) {}
}
