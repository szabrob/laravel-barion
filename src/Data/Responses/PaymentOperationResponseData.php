<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Responses;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Szabrob\LaravelBarion\Data\Concerns\IgnoresNullInput;
use Szabrob\LaravelBarion\Enums\PaymentStatus;

/**
 * Shared response of the FinishReservation, Capture and CancelAuthorization endpoints.
 */
#[MapInputName(StudlyCaseMapper::class)]
final class PaymentOperationResponseData extends Data
{
    use IgnoresNullInput;

    /**
     * @param  array<int, TransactionResponseData>  $transactions
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly PaymentStatus $status,
        public readonly bool $isSuccessful = false,
        public readonly ?string $paymentRequestId = null,
        #[DataCollectionOf(TransactionResponseData::class)]
        public readonly array $transactions = [],
    ) {}
}
