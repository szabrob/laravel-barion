<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Requests;

use LaravelBarion\LaravelBarion\Data\ItemData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapOutputName(StudlyCaseMapper::class)]
final class PaymentTransactionData extends Data
{
    /**
     * @param  array<int, ItemData>  $items
     * @param  array<int, PayeeTransactionData>  $payeeTransactions
     * @param  string|null  $payee  Defaults to the configured payee of the active environment.
     */
    public function __construct(
        #[MapOutputName('POSTransactionId')]
        public readonly string $posTransactionId,
        public readonly float $total,
        #[DataCollectionOf(ItemData::class)]
        public readonly array $items,
        public readonly ?string $payee = null,
        public readonly ?string $comment = null,
        #[DataCollectionOf(PayeeTransactionData::class)]
        public readonly array $payeeTransactions = [],
    ) {}
}
