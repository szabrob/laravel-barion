<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Responses;

use Carbon\CarbonImmutable;
use LaravelBarion\LaravelBarion\Data\Casts\BarionDateTimeCast;
use LaravelBarion\LaravelBarion\Data\Casts\LenientEnumCast;
use LaravelBarion\LaravelBarion\Data\Concerns\IgnoresNullInput;
use LaravelBarion\LaravelBarion\Enums\Currency;
use LaravelBarion\LaravelBarion\Enums\PaymentStatus;
use LaravelBarion\LaravelBarion\Enums\PaymentType;
use LaravelBarion\LaravelBarion\Enums\RecurrenceType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * Response of the v4 Payment/{paymentId}/PaymentState endpoint: the only
 * trustworthy source of a payment's state.
 */
#[MapInputName(StudlyCaseMapper::class)]
final class PaymentStateData extends Data
{
    use IgnoresNullInput;

    /**
     * @param  array<int, string>  $allowedFundingSources
     * @param  array<int, TransactionDetailData>  $transactions
     */
    public function __construct(
        public readonly string $paymentId,
        public readonly PaymentStatus $status,
        public readonly ?string $paymentRequestId = null,
        public readonly ?string $orderNumber = null,
        #[MapInputName('POSId')]
        public readonly ?string $posId = null,
        #[MapInputName('POSName')]
        public readonly ?string $posName = null,
        #[MapInputName('POSOwnerEmail')]
        public readonly ?string $posOwnerEmail = null,
        #[MapInputName('POSOwnerCountry')]
        public readonly ?string $posOwnerCountry = null,
        #[WithCast(LenientEnumCast::class, PaymentType::class)]
        public readonly ?PaymentType $paymentType = null,
        public readonly ?string $fundingSource = null,
        public readonly ?FundingInformationData $fundingInformation = null,
        public readonly array $allowedFundingSources = [],
        public readonly ?bool $guestCheckout = null,
        #[WithCast(BarionDateTimeCast::class)]
        public readonly ?CarbonImmutable $createdAt = null,
        #[WithCast(BarionDateTimeCast::class)]
        public readonly ?CarbonImmutable $validUntil = null,
        #[WithCast(BarionDateTimeCast::class)]
        public readonly ?CarbonImmutable $completedAt = null,
        #[WithCast(BarionDateTimeCast::class)]
        public readonly ?CarbonImmutable $reservedUntil = null,
        #[WithCast(BarionDateTimeCast::class)]
        public readonly ?CarbonImmutable $delayedCaptureUntil = null,
        public readonly ?float $total = null,
        #[WithCast(LenientEnumCast::class, Currency::class)]
        public readonly ?Currency $currency = null,
        #[DataCollectionOf(TransactionDetailData::class)]
        public readonly array $transactions = [],
        public readonly ?string $suggestedLocale = null,
        public readonly ?float $fraudRiskScore = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $callbackUrl = null,
        #[WithCast(LenientEnumCast::class, RecurrenceType::class)]
        public readonly ?RecurrenceType $recurrenceType = null,
        public readonly ?string $traceId = null,
        public readonly ?string $paymentMethod = null,
    ) {}
}
