<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Requests;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * Request body of the v2 Payment/FinishReservation endpoint.
 */
#[MapOutputName(StudlyCaseMapper::class)]
final class FinishReservationRequestData extends Data
{
    /**
     * @param  array<int, TransactionToFinishData>  $transactions
     */
    public function __construct(
        public readonly string $paymentId,
        #[DataCollectionOf(TransactionToFinishData::class)]
        public readonly array $transactions,
        #[Hidden]
        public readonly ?string $idempotencyKey = null,
    ) {}
}
