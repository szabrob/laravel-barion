<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums\ThreeDSecure;

enum SuspiciousActivityIndicator: string
{
    case NoSuspiciousActivityObserved = 'NoSuspiciousActivityObserved';
    case SuspiciousActivityObserved = 'SuspiciousActivityObserved';
}
