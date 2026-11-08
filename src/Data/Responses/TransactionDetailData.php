<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Responses;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Szabrob\LaravelBarion\Data\Casts\BarionDateTimeCast;
use Szabrob\LaravelBarion\Data\Casts\LenientEnumCast;
use Szabrob\LaravelBarion\Data\Concerns\IgnoresNullInput;
use Szabrob\LaravelBarion\Enums\Currency;
use Szabrob\LaravelBarion\Enums\TransactionStatus;

#[MapInputName(StudlyCaseMapper::class)]
final class TransactionDetailData extends Data
{
    use IgnoresNullInput;

    /**
     * @param  array<int, TransactionItemData>  $items
     */
    public function __construct(
        public readonly ?string $transactionId = null,
        #[MapInputName('POSTransactionId')]
        public readonly ?string $posTransactionId = null,
        #[WithCast(BarionDateTimeCast::class)]
        public readonly ?CarbonImmutable $transactionTime = null,
        public readonly ?float $total = null,
        #[WithCast(LenientEnumCast::class, Currency::class)]
        public readonly ?Currency $currency = null,
        public readonly ?UserData $payer = null,
        public readonly ?UserData $payee = null,
        public readonly ?string $comment = null,
        #[WithCast(LenientEnumCast::class, TransactionStatus::class)]
        public readonly ?TransactionStatus $status = null,
        public readonly ?string $transactionType = null,
        #[DataCollectionOf(TransactionItemData::class)]
        public readonly array $items = [],
        public readonly ?string $relatedId = null,
        #[MapInputName('POSId')]
        public readonly ?string $posId = null,
        public readonly ?string $paymentId = null,
    ) {}
}
