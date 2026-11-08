<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\ThreeDSecure;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapOutputName(StudlyCaseMapper::class)]
final class GiftCardPurchaseData extends Data
{
    public function __construct(
        public readonly float $amount,
        public readonly int $count,
    ) {}
}
