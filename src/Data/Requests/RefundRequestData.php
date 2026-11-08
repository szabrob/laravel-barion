<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Requests;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * Request body of the v2 Payment/Refund endpoint.
 */
#[MapOutputName(StudlyCaseMapper::class)]
final class RefundRequestData extends Data
{
    /**
     * @param  array<int, TransactionToRefundData>  $transactionsToRefund
     */
    public function __construct(
        public readonly string $paymentId,
        #[DataCollectionOf(TransactionToRefundData::class)]
        public readonly array $transactionsToRefund,
        #[Hidden]
        public readonly ?string $idempotencyKey = null,
    ) {}
}
