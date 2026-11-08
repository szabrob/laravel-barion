<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LaravelBarion\LaravelBarion\Data\Requests\CancelAuthorizationRequestData;
use LaravelBarion\LaravelBarion\Data\Requests\CaptureRequestData;
use LaravelBarion\LaravelBarion\Data\Requests\FinishReservationRequestData;
use LaravelBarion\LaravelBarion\Data\Requests\RefundRequestData;
use LaravelBarion\LaravelBarion\Data\Requests\TransactionToFinishData;
use LaravelBarion\LaravelBarion\Data\Requests\TransactionToRefundData;
use LaravelBarion\LaravelBarion\Enums\PaymentStatus;
use LaravelBarion\LaravelBarion\Enums\TransactionStatus;
use LaravelBarion\LaravelBarion\Facades\LaravelBarion;

function operationResponse(string $status): array
{
    return [
        'IsSuccessful' => true,
        'PaymentId' => PAYMENT_ID,
        'PaymentRequestId' => 'order-1001',
        'Status' => $status,
        'Transactions' => [['POSTransactionId' => 'order-1001-t1', 'TransactionId' => 'trx-1', 'Status' => 'Succeeded']],
        'Errors' => [],
    ];
}

function assertBarionPost(string $path, array $body): void
{
    Http::assertSent(function (Request $request) use ($path, $body): bool {
        expect($request->method())->toBe('POST')
            ->and($request->url())->toBe('https://api.test.barion.com'.$path)
            ->and($request->header('x-pos-key'))->toBe(['test-pos-key'])
            ->and($request->header('Idempotency-Key'))->toHaveCount(1)
            ->and($request->data())->toBe($body);

        return true;
    });
}

function refundRequest(string $refundId): RefundRequestData
{
    return new RefundRequestData(PAYMENT_ID, [
        new TransactionToRefundData(transactionId: 'trx-1', posTransactionId: $refundId, amountToRefund: 500, comment: 'Returned item'),
    ]);
}

it('refunds a payment', function (): void {
    Http::fake(['*' => Http::response([
        'PaymentId' => PAYMENT_ID,
        'RefundedTransactions' => [['TransactionId' => 'trx-r1', 'Total' => 500, 'POSTransactionId' => 'refund-1', 'Comment' => 'Returned item', 'Status' => 'Succeeded']],
        'Errors' => [],
    ])]);

    $response = LaravelBarion::refund(refundRequest('refund-1'));

    expect($response->paymentId)->toBe(PAYMENT_ID)
        ->and($response->refundedTransactions[0]->total)->toBe(500.0)
        ->and($response->refundedTransactions[0]->status)->toBe(TransactionStatus::Succeeded);

    assertBarionPost('/v2/Payment/Refund', [
        'PaymentId' => PAYMENT_ID,
        'TransactionsToRefund' => [[
            'TransactionId' => 'trx-1',
            'POSTransactionId' => 'refund-1',
            'AmountToRefund' => 500.0,
            'Comment' => 'Returned item',
        ]],
    ]);
});

it('gives separate refunds separate idempotency keys', function (): void {
    Http::fake(['*' => Http::response(['PaymentId' => PAYMENT_ID])]);

    LaravelBarion::refund(refundRequest('refund-1'));
    LaravelBarion::refund(refundRequest('refund-1'));
    LaravelBarion::refund(refundRequest('refund-2'));

    $keys = Http::recorded()->map(fn (array $pair): string => $pair[0]->header('Idempotency-Key')[0])->all();

    expect($keys[1])->toBe($keys[0])->and($keys[2])->not->toBe($keys[0]);
});

it('finishes a reservation', function (): void {
    Http::fake(['*' => Http::response(operationResponse('Succeeded'))]);

    $response = LaravelBarion::finishReservation(new FinishReservationRequestData(PAYMENT_ID, [
        new TransactionToFinishData(transactionId: 'trx-1', total: 1200),
    ]));

    expect($response->isSuccessful)->toBeTrue()
        ->and($response->status)->toBe(PaymentStatus::Succeeded)
        ->and($response->transactions[0]->transactionId)->toBe('trx-1');

    assertBarionPost('/v2/Payment/FinishReservation', [
        'PaymentId' => PAYMENT_ID,
        'Transactions' => [['TransactionId' => 'trx-1', 'Total' => 1200.0, 'Items' => [], 'PayeeTransactions' => []]],
    ]);
});

it('captures a delayed capture payment', function (): void {
    Http::fake(['*' => Http::response(operationResponse('Succeeded'))]);

    $response = LaravelBarion::capture(new CaptureRequestData(PAYMENT_ID, [
        new TransactionToFinishData(transactionId: 'trx-1', total: 1500, comment: 'Shipped'),
    ]));

    expect($response->status)->toBe(PaymentStatus::Succeeded);

    assertBarionPost('/v2/Payment/Capture', [
        'PaymentId' => PAYMENT_ID,
        'Transactions' => [['TransactionId' => 'trx-1', 'Total' => 1500.0, 'Items' => [], 'PayeeTransactions' => [], 'Comment' => 'Shipped']],
    ]);
});

it('cancels an authorization', function (): void {
    Http::fake(['*' => Http::response(operationResponse('Canceled'))]);

    $response = LaravelBarion::cancelAuthorization(new CancelAuthorizationRequestData(PAYMENT_ID));

    expect($response->status)->toBe(PaymentStatus::Canceled);

    assertBarionPost('/v2/Payment/CancelAuthorization', ['PaymentId' => PAYMENT_ID]);
});
