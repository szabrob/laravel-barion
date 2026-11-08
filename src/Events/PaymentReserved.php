<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Events;

use Szabrob\LaravelBarion\Data\Responses\PaymentStateData;

/**
 * Dispatched when the verified payment state is Reserved (Reservation payments): finish it with finishReservation().
 */
final class PaymentReserved
{
    public function __construct(public readonly PaymentStateData $paymentState) {}
}
