<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Responses;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
final class ApiErrorData extends Data
{
    public function __construct(
        public readonly ?string $errorCode = null,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $endPoint = null,
        public readonly ?string $paymentId = null,
        public readonly ?string $happenedAt = null,
    ) {}
}
