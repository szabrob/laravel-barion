<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums;

enum PaymentType: string
{
    case Immediate = 'Immediate';
    case Reservation = 'Reservation';
    case DelayedCapture = 'DelayedCapture';
}
