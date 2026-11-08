<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Events;

use LaravelBarion\LaravelBarion\Data\Responses\PaymentStateData;

/**
 * Dispatched when the verified payment state is Canceled, Failed or Expired.
 */
final class PaymentFailed
{
    public function __construct(public readonly PaymentStateData $paymentState) {}
}
