<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\ThreeDSecure;

use DateTimeInterface;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\AccountChangeIndicator;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\AccountCreationIndicator;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\PasswordChangeIndicator;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\PaymentMethodIndicator;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\ShippingAddressUsageIndicator;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\SuspiciousActivityIndicator;

#[MapOutputName(StudlyCaseMapper::class)]
final class PayerAccountInformationData extends Data
{
    public function __construct(
        public readonly ?string $accountId = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s', setTimeZone: 'UTC')]
        public readonly ?DateTimeInterface $accountCreated = null,
        public readonly ?AccountCreationIndicator $accountCreationIndicator = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s', setTimeZone: 'UTC')]
        public readonly ?DateTimeInterface $accountLastChanged = null,
        public readonly ?AccountChangeIndicator $accountChangeIndicator = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s', setTimeZone: 'UTC')]
        public readonly ?DateTimeInterface $passwordLastChanged = null,
        public readonly ?PasswordChangeIndicator $passwordChangeIndicator = null,
        public readonly ?int $purchasesInTheLastSixMonths = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s', setTimeZone: 'UTC')]
        public readonly ?DateTimeInterface $shippingAddressAdded = null,
        public readonly ?ShippingAddressUsageIndicator $shippingAddressUsageIndicator = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s', setTimeZone: 'UTC')]
        public readonly ?DateTimeInterface $paymentMethodAdded = null,
        public readonly ?PaymentMethodIndicator $paymentMethodIndicator = null,
        public readonly ?int $provisionAttempts = null,
        public readonly ?int $transactionalActivityPerDay = null,
        public readonly ?int $transactionalActivityPerYear = null,
        public readonly ?SuspiciousActivityIndicator $suspiciousActivityIndicator = null,
    ) {}
}
