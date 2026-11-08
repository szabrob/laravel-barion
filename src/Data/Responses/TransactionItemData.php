<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Responses;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Szabrob\LaravelBarion\Data\Concerns\IgnoresNullInput;

/**
 * An item as returned by Barion. Unlike the request-side ItemData, every
 * field is optional, so an incomplete item never breaks response handling.
 */
#[MapInputName(StudlyCaseMapper::class)]
final class TransactionItemData extends Data
{
    use IgnoresNullInput;

    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?float $quantity = null,
        public readonly ?string $unit = null,
        public readonly ?float $unitPrice = null,
        public readonly ?float $itemTotal = null,
        #[MapInputName('SKU')]
        public readonly ?string $sku = null,
    ) {}
}
