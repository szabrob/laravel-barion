<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Events;

use Szabrob\LaravelBarion\Data\Responses\PaymentStateData;

/**
 * Dispatched when the verified payment state contains succeeded refund transactions. Every later callback of the payment dispatches it again, so deduplicate by the refund transaction ids ($paymentState->refundTransactions()).
 */
final class PaymentRefunded
{
    public function __construct(public readonly PaymentStateData $paymentState) {}
}
