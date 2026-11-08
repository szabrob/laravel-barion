<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Events;

use Szabrob\LaravelBarion\Data\Responses\PaymentStateData;

/**
 * Dispatched when the verified payment state is Canceled, Failed or Expired.
 */
final class PaymentFailed
{
    public function __construct(public readonly PaymentStateData $paymentState) {}
}
