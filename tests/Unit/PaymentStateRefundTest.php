<?php

declare(strict_types=1);

use Szabrob\LaravelBarion\Data\Responses\PaymentStateData;
use Szabrob\LaravelBarion\Data\Responses\TransactionDetailData;

it('lists only succeeded refund transactions', function (): void {
    $state = PaymentStateData::from(refundedPaymentStateResponse());

    expect(array_map(fn (TransactionDetailData $transaction): ?string => $transaction->transactionId, $state->refundTransactions()))->toBe(['trx-refund-1'])
        ->and($state->refundedAmount())->toBe(500.0);
});

it('recognizes every refund transaction type', function (string $type): void {
    $state = PaymentStateData::from([...paymentStateResponse(), 'Transactions' => [
        ['TransactionId' => 'r1', 'Total' => 10.5, 'Status' => 'Succeeded', 'TransactionType' => $type],
        ['TransactionId' => 'r2', 'Total' => 0.2, 'Status' => 'Succeeded', 'TransactionType' => $type],
    ]]);

    expect($state->refundTransactions())->toHaveCount(2)
        ->and($state->refundedAmount())->toBe(10.7);
})->with(['Refund', 'RefundToBankCard', 'RefundToBankAccount']);

it('has no refunds for a plain payment', function (): void {
    $state = PaymentStateData::from(paymentStateResponse());

    expect($state->refundTransactions())->toBe([])
        ->and($state->refundedAmount())->toBe(0.0);
});
