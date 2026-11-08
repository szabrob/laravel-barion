<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\ThreeDSecure;

use DateTimeInterface;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\AccountChangeIndicator;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\AccountCreationIndicator;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\PasswordChangeIndicator;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\PaymentMethodIndicator;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\ShippingAddressUsageIndicator;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\SuspiciousActivityIndicator;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

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
