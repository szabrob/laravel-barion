<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Requests;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * A sub-transaction that forwards part of the amount to another Barion wallet.
 */
#[MapOutputName(StudlyCaseMapper::class)]
final class PayeeTransactionData extends Data
{
    public function __construct(
        #[MapOutputName('POSTransactionId')]
        public readonly string $posTransactionId,
        public readonly string $payee,
        public readonly float $total,
        public readonly ?string $comment = null,
    ) {}
}
