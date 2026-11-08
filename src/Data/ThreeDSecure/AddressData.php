<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\ThreeDSecure;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

/**
 * Shipping or billing address. FullName is only sent for shipping addresses.
 */
#[MapOutputName(StudlyCaseMapper::class)]
final class AddressData extends Data
{
    /**
     * @param  string  $country  ISO-3166-1 alpha-2 country code.
     */
    public function __construct(
        public readonly string $country,
        public readonly string $city,
        public readonly string $zip,
        public readonly string $street,
        public readonly ?string $region = null,
        public readonly ?string $street2 = null,
        public readonly ?string $street3 = null,
        public readonly ?string $fullName = null,
    ) {}
}
