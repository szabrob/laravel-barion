<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Responses;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Szabrob\LaravelBarion\Data\Concerns\IgnoresNullInput;

#[MapInputName(StudlyCaseMapper::class)]
final class RefundResponseData extends Data
{
    use IgnoresNullInput;

    /**
     * @param  array<int, RefundedTransactionData>  $refundedTransactions
     */
    public function __construct(
        public readonly string $paymentId,
        #[DataCollectionOf(RefundedTransactionData::class)]
        public readonly array $refundedTransactions = [],
    ) {}
}
