<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Responses;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Szabrob\LaravelBarion\Data\Casts\LenientEnumCast;
use Szabrob\LaravelBarion\Data\Concerns\IgnoresNullInput;
use Szabrob\LaravelBarion\Enums\TransactionStatus;

#[MapInputName(StudlyCaseMapper::class)]
final class RefundedTransactionData extends Data
{
    use IgnoresNullInput;

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
