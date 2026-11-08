<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Responses;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Szabrob\LaravelBarion\Data\Casts\LenientEnumCast;
use Szabrob\LaravelBarion\Data\Concerns\IgnoresNullInput;
use Szabrob\LaravelBarion\Enums\PaymentStatus;
use Szabrob\LaravelBarion\Enums\RecurrenceResult;

/**
 * Response of the v2 Payment/Start endpoint. Redirect the customer to $gatewayUrl.
 */
#[MapInputName(StudlyCaseMapper::class)]
final class PaymentResponseData extends Data
{
    use IgnoresNullInput;

    /**
     * @param  array<int, TransactionResponseData>  $transactions
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly PaymentStatus $status,
        public readonly string $gatewayUrl,
        public readonly ?string $paymentRequestId = null,
        #[DataCollectionOf(TransactionResponseData::class)]
        public readonly array $transactions = [],
        #[MapInputName('QRUrl')]
        public readonly ?string $qrUrl = null,
        #[WithCast(LenientEnumCast::class, RecurrenceResult::class)]
        public readonly ?RecurrenceResult $recurrenceResult = null,
        public readonly ?string $threeDSAuthClientData = null,
        public readonly ?string $traceId = null,
    ) {}
}
