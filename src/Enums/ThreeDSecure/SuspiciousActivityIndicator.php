<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums\ThreeDSecure;

enum SuspiciousActivityIndicator: string
{
    case NoSuspiciousActivityObserved = 'NoSuspiciousActivityObserved';
    case SuspiciousActivityObserved = 'SuspiciousActivityObserved';
}
