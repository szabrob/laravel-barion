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
use Szabrob\LaravelBarion\Enums\PaymentStatus;
use Szabrob\LaravelBarion\Enums\PaymentType;
use Szabrob\LaravelBarion\Enums\RecurrenceType;
use Szabrob\LaravelBarion\Enums\TransactionStatus;

/**
 * Response of the v4 Payment/{paymentId}/PaymentState endpoint: the only
 * trustworthy source of a payment's state.
 */
#[MapInputName(StudlyCaseMapper::class)]
final class PaymentStateData extends Data
{
    use IgnoresNullInput;

    /**
     * Transaction types of money sent back to the payer. The "Storno..."
     * types mark failed refunds and are not included.
     */
    public const array REFUND_TRANSACTION_TYPES = ['Refund', 'RefundToBankCard', 'RefundToBankAccount'];

    /**
     * @param  array<int, string>  $allowedFundingSources
     * @param  array<int, TransactionDetailData>  $transactions
     * @param  string|null  $traceId  The 3D Secure trace id of a token payment. Store it from the first payment and send it with every later charge of the token. Barion does not return the recurrenceId here: keep the one you generated.
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

    /**
     * The succeeded refund transactions of the payment.
     *
     * @return array<int, TransactionDetailData>
     */
    public function refundTransactions(): array
    {
        return array_values(array_filter(
            $this->transactions,
            fn (TransactionDetailData $transaction): bool => $transaction->status === TransactionStatus::Succeeded
                && in_array($transaction->transactionType, self::REFUND_TRANSACTION_TYPES, true),
        ));
    }

    public function refundedAmount(): float
    {
        return round(array_sum(array_map(
            fn (TransactionDetailData $transaction): float => $transaction->total ?? 0.0,
            $this->refundTransactions(),
        )), 2);
    }
}
