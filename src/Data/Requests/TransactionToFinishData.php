<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Requests;

use LaravelBarion\LaravelBarion\Data\ItemData;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * A transaction to finish (Reservation) or capture (DelayedCapture) with its final amount.
 */
#[MapOutputName(StudlyCaseMapper::class)]
final class TransactionToFinishData extends Data
{
    /**
     * @param  array<int, ItemData>  $items
     * @param  array<int, PayeeTransactionToFinishData>  $payeeTransactions
     */
    public function __construct(
        public readonly string $transactionId,
        public readonly float $total,
        #[DataCollectionOf(ItemData::class)]
        public readonly array $items = [],
        #[DataCollectionOf(PayeeTransactionToFinishData::class)]
        public readonly array $payeeTransactions = [],
        public readonly ?string $comment = null,
    ) {}
}
