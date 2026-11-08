<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Requests;

use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * Request body of the v2 Payment/CancelAuthorization endpoint (DelayedCapture payments).
 */
#[MapOutputName(StudlyCaseMapper::class)]
final class CancelAuthorizationRequestData extends Data
{
    public function __construct(
        public readonly string $paymentId,
        #[Hidden]
        public readonly ?string $idempotencyKey = null,
    ) {}
}
