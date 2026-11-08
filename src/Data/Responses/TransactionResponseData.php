<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Responses;

use Carbon\CarbonImmutable;
use LaravelBarion\LaravelBarion\Data\Casts\BarionDateTimeCast;
use LaravelBarion\LaravelBarion\Data\Casts\LenientEnumCast;
use LaravelBarion\LaravelBarion\Data\Concerns\IgnoresNullInput;
use LaravelBarion\LaravelBarion\Enums\TransactionStatus;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
final class TransactionResponseData extends Data
{
    use IgnoresNullInput;

    public function __construct(
        #[MapInputName('POSTransactionId')]
        public readonly ?string $posTransactionId = null,
        public readonly ?string $transactionId = null,
        #[WithCast(LenientEnumCast::class, TransactionStatus::class)]
        public readonly ?TransactionStatus $status = null,
        #[WithCast(BarionDateTimeCast::class)]
        public readonly ?CarbonImmutable $transactionTime = null,
        public readonly ?string $relatedId = null,
    ) {}
}
