<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Events;

use Szabrob\LaravelBarion\Data\Responses\PaymentStateData;

/**
 * Dispatched when the verified payment state is Authorized (DelayedCapture payments): capture() or cancelAuthorization() it.
 */
final class PaymentAuthorized
{
    public function __construct(public readonly PaymentStateData $paymentState) {}
}
