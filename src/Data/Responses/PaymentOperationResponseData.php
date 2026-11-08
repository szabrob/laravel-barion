<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Responses;

use LaravelBarion\LaravelBarion\Enums\PaymentStatus;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * Shared response of the FinishReservation, Capture and CancelAuthorization endpoints.
 */
#[MapInputName(StudlyCaseMapper::class)]
final class PaymentOperationResponseData extends Data
{
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
