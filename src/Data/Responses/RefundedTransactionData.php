<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Responses;

use LaravelBarion\LaravelBarion\Data\Casts\LenientEnumCast;
use LaravelBarion\LaravelBarion\Enums\TransactionStatus;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
final class RefundedTransactionData extends Data
{
    public function __construct(
        public readonly ?string $transactionId = null,
        public readonly ?float $total = null,
        #[MapInputName('POSTransactionId')]
        public readonly ?string $posTransactionId = null,
        public readonly ?string $comment = null,
        #[WithCast(LenientEnumCast::class, TransactionStatus::class)]
        public readonly ?TransactionStatus $status = null,
    ) {}
}
