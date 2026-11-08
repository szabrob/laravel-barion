<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Requests;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapOutputName(StudlyCaseMapper::class)]
final class PayeeTransactionToFinishData extends Data
{
    public function __construct(
        public readonly string $transactionId,
        public readonly float $total,
        public readonly ?string $comment = null,
    ) {}
}
