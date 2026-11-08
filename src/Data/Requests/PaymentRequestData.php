<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Requests;

use LaravelBarion\LaravelBarion\Data\ThreeDSecure\AddressData;
use LaravelBarion\LaravelBarion\Data\ThreeDSecure\PayerAccountInformationData;
use LaravelBarion\LaravelBarion\Data\ThreeDSecure\PurchaseInformationData;
use LaravelBarion\LaravelBarion\Enums\Currency;
use LaravelBarion\LaravelBarion\Enums\FundingSource;
use LaravelBarion\LaravelBarion\Enums\Locale;
use LaravelBarion\LaravelBarion\Enums\PaymentType;
use LaravelBarion\LaravelBarion\Enums\RecurrenceType;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\ChallengePreference;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * Request body of the v2 Payment/Start endpoint.
 */
#[MapOutputName(StudlyCaseMapper::class)]
final class PaymentRequestData extends Data
{
    /**
     * @param  array<int, PaymentTransactionData>  $transactions
     * @param  array<int, FundingSource>  $fundingSources
     * @param  string  $paymentWindow  Format: d.hh:mm:ss or hh:mm:ss.
     * @param  string|null  $idempotencyKey  Sent as the Idempotency-Key header. Derived from the request when null.
     */
    public function __construct(
        public readonly string $paymentRequestId,
        #[DataCollectionOf(PaymentTransactionData::class)]
        public readonly array $transactions,
        public readonly Currency $currency = Currency::HUF,
        public readonly Locale $locale = Locale::Hungarian,
        public readonly PaymentType $paymentType = PaymentType::Immediate,
        public readonly bool $guestCheckOut = true,
        public readonly array $fundingSources = [FundingSource::All],
        public readonly string $paymentWindow = '00:30:00',
        public readonly ?string $redirectUrl = null,
        public readonly ?string $callbackUrl = null,
        public readonly ?string $orderNumber = null,
        public readonly ?string $payerHint = null,
        public readonly ?string $cardHolderNameHint = null,
        public readonly ?string $reservationPeriod = null,
        public readonly ?string $delayedCapturePeriod = null,
        public readonly bool $initiateRecurrence = false,
        public readonly ?string $recurrenceId = null,
        public readonly ?RecurrenceType $recurrenceType = null,
        public readonly ?ChallengePreference $challengePreference = null,
        public readonly ?string $payerPhoneNumber = null,
        public readonly ?string $payerWorkPhoneNumber = null,
        public readonly ?string $payerHomePhoneNumber = null,
        public readonly ?AddressData $shippingAddress = null,
        public readonly ?AddressData $billingAddress = null,
        public readonly ?PayerAccountInformationData $payerAccountInformation = null,
        public readonly ?PurchaseInformationData $purchaseInformation = null,
        public readonly ?string $traceId = null,
        #[Hidden]
        public readonly ?string $idempotencyKey = null,
    ) {}
}
