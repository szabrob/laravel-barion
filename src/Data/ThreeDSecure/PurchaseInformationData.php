<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\ThreeDSecure;

use DateTimeInterface;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\AvailabilityIndicator;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\DeliveryTimeframe;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\PurchaseType;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\ReOrderIndicator;
use LaravelBarion\LaravelBarion\Enums\ThreeDSecure\ShippingAddressIndicator;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

#[MapOutputName(StudlyCaseMapper::class)]
final class PurchaseInformationData extends Data
{
    public function __construct(
        public readonly ?DeliveryTimeframe $deliveryTimeframe = null,
        public readonly ?string $deliveryEmailAddress = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s', setTimeZone: 'UTC')]
        public readonly ?DateTimeInterface $preOrderDate = null,
        public readonly ?AvailabilityIndicator $availabilityIndicator = null,
        public readonly ?ReOrderIndicator $reOrderIndicator = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s', setTimeZone: 'UTC')]
        public readonly ?DateTimeInterface $recurringExpiry = null,
        public readonly ?int $recurringFrequency = null,
        public readonly ?ShippingAddressIndicator $shippingAddressIndicator = null,
        public readonly ?GiftCardPurchaseData $giftCardPurchase = null,
        public readonly ?PurchaseType $purchaseType = null,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'Y-m-d\TH:i:s', setTimeZone: 'UTC')]
        public readonly ?DateTimeInterface $purchaseDate = null,
    ) {}
}
