<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Requests;

use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * Request body of the v2 Payment/Complete endpoint: completes a token payment
 * after the customer finished the 3D Secure authentication off-site.
 */
#[MapOutputName(StudlyCaseMapper::class)]
final class CompletePaymentRequestData extends Data
{
    public function __construct(
        public readonly string $paymentId,
        #[Hidden]
        public readonly ?string $idempotencyKey = null,
    ) {}
}
