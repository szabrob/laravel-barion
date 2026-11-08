<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LaravelBarion\LaravelBarion\Data\Requests\PaymentRequestData;
use LaravelBarion\LaravelBarion\Facades\LaravelBarion;

/**
 * @return array<int, string>
 */
function sentIdempotencyKeys(): array
{
    return Http::recorded()
        ->map(fn (array $pair): string => $pair[0]->header('Idempotency-Key')[0] ?? '')
        ->all();
}

it('sends an idempotency key with every payment start by default', function (): void {
    Http::fake(['*' => Http::response(['PaymentId' => PAYMENT_ID, 'Status' => 'Prepared'])]);

    LaravelBarion::startPayment(paymentRequest());

    expect(sentIdempotencyKeys()[0])->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-8[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/');
});

it('derives the same key for the same request so retries cannot start a second payment', function (): void {
    Http::fake(['*' => Http::response(['PaymentId' => PAYMENT_ID, 'Status' => 'Prepared'])]);

    LaravelBarion::startPayment(paymentRequest());
    LaravelBarion::startPayment(paymentRequest());
    LaravelBarion::startPayment(paymentRequest('order-1002'));

    [$first, $retry, $other] = sentIdempotencyKeys();

    expect($retry)->toBe($first)->and($other)->not->toBe($first);
});

it('derives different keys per environment', function (): void {
    Http::fake(['*' => Http::response(['PaymentId' => PAYMENT_ID, 'Status' => 'Prepared'])]);

    LaravelBarion::startPayment(paymentRequest());
    config()->set('laravel-barion.environment', 'production');
    LaravelBarion::startPayment(paymentRequest());

    [$test, $production] = sentIdempotencyKeys();

    expect($production)->not->toBe($test);
});

it('uses an explicit idempotency key and never sends it in the body', function (): void {
    Http::fake(['*' => Http::response(['PaymentId' => PAYMENT_ID, 'Status' => 'Prepared'])]);

    LaravelBarion::startPayment(PaymentRequestData::from([
        'paymentRequestId' => 'order-1001',
        'transactions' => [[
            'posTransactionId' => 'order-1001-t1',
            'total' => 1500,
            'items' => [['name' => 'T-shirt', 'description' => 'Black, M', 'quantity' => 1, 'unit' => 'pcs', 'unitPrice' => 1500, 'itemTotal' => 1500]],
        ]],
        'idempotencyKey' => 'my-own-key',
    ]));

    Http::assertSent(fn (Request $request): bool => $request->header('Idempotency-Key') === ['my-own-key']
        && ! str_contains($request->body(), 'my-own-key')
        && ! array_key_exists('IdempotencyKey', $request->data()));
});

it('retries server errors with the same idempotency key', function (): void {
    Http::fake(['*' => Http::sequence()
        ->push('Service Unavailable', 503)
        ->push(['PaymentId' => PAYMENT_ID, 'Status' => 'Prepared']),
    ]);

    LaravelBarion::startPayment(paymentRequest());

    $keys = sentIdempotencyKeys();

    expect($keys)->toHaveCount(2)->and($keys[1])->toBe($keys[0]);
});
