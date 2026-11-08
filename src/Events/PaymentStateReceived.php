<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Events;

use LaravelBarion\LaravelBarion\Data\Responses\PaymentStateData;

/**
 * Dispatched for every callback, with the state queried from the PaymentState API.
 */
final class PaymentStateReceived
{
    public function __construct(public readonly PaymentStateData $paymentState) {}
}
