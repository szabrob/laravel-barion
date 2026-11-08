<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums\ThreeDSecure;

enum DeliveryTimeframe: string
{
    case ElectronicDelivery = 'ElectronicDelivery';
    case SameDayShipping = 'SameDayShipping';
    case OvernightShipping = 'OvernightShipping';
    case TwoDayOrMoreShipping = 'TwoDayOrMoreShipping';
}
