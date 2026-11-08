<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Events;

use Szabrob\LaravelBarion\Data\Responses\PaymentStateData;

/**
 * Dispatched when the verified payment state is Succeeded. Callbacks may repeat (e.g. after a refund), so listeners must be idempotent.
 */
final class PaymentSucceeded
{
    public function __construct(public readonly PaymentStateData $paymentState) {}
}
