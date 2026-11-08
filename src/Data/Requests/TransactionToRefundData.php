<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Requests;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapOutputName(StudlyCaseMapper::class)]
final class TransactionToRefundData extends Data
{
    /**
     * @param  string  $transactionId  The Barion transaction id of the original payment transaction.
     * @param  string  $posTransactionId  Your own, unique identifier of this refund.
     */
    public function __construct(
        public readonly string $transactionId,
        #[MapOutputName('POSTransactionId')]
        public readonly string $posTransactionId,
        public readonly float $amountToRefund,
        public readonly ?string $comment = null,
    ) {}
}
