<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\ThreeDSecure;

use DateTimeInterface;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\AvailabilityIndicator;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\DeliveryTimeframe;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\PurchaseType;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\ReOrderIndicator;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\ShippingAddressIndicator;

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
