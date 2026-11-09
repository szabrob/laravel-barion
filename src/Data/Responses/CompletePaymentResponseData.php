<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Responses;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Szabrob\LaravelBarion\Data\Concerns\IgnoresNullInput;
use Szabrob\LaravelBarion\Enums\PaymentStatus;

/**
 * Response of the v2 Payment/Complete endpoint. Barion sends the status in
 * the PaymentStatus field here, not in Status like the other operations.
 */
#[MapInputName(StudlyCaseMapper::class)]
final class CompletePaymentResponseData extends Data
{
    use IgnoresNullInput;

    public function __construct(
        public readonly string $paymentId,
        #[MapInputName('PaymentStatus')]
        public readonly PaymentStatus $status,
        public readonly bool $isSuccessful = false,
        public readonly ?string $paymentRequestId = null,
        public readonly ?string $traceId = null,
    ) {}
}
