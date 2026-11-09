<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Events;

use Szabrob\LaravelBarion\Data\Responses\PaymentStateData;

/**
 * Dispatched when the verified payment state is PartiallySucceeded: a Reservation payment was finished with only some of its transactions (or a lower amount). Read the transactions and the total from $paymentState.
 */
final class PaymentPartiallySucceeded
{
    public function __construct(public readonly PaymentStateData $paymentState) {}
}
