<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums\ThreeDSecure;

enum ReOrderIndicator: string
{
    case FirstTimeOrdered = 'FirstTimeOrdered';
    case ReOrdered = 'ReOrdered';
}
