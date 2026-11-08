<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums\ThreeDSecure;

enum ReOrderIndicator: string
{
    case FirstTimeOrdered = 'FirstTimeOrdered';
    case ReOrdered = 'ReOrdered';
}
