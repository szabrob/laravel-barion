<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums\ThreeDSecure;

enum AvailabilityIndicator: string
{
    case MerchandiseAvailable = 'MerchandiseAvailable';
    case FutureAvailability = 'FutureAvailability';
}
