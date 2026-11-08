<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Events;

use Szabrob\LaravelBarion\Data\Responses\PaymentStateData;

/**
 * Dispatched for every callback, with the state queried from the PaymentState API.
 */
final class PaymentStateReceived
{
    public function __construct(public readonly PaymentStateData $paymentState) {}
}
