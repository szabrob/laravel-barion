<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Szabrob\LaravelBarion\Data\Responses\TransactionDetailData;
use Szabrob\LaravelBarion\Enums\PaymentStatus;
use Szabrob\LaravelBarion\Events\PaymentAuthorized;
use Szabrob\LaravelBarion\Events\PaymentFailed;
use Szabrob\LaravelBarion\Events\PaymentPartiallySucceeded;
use Szabrob\LaravelBarion\Events\PaymentRefunded;
use Szabrob\LaravelBarion\Events\PaymentReserved;
use Szabrob\LaravelBarion\Events\PaymentStateReceived;
use Szabrob\LaravelBarion\Events\PaymentSucceeded;
use Szabrob\LaravelBarion\Exceptions\BarionApiException;
use Szabrob\LaravelBarion\LaravelBarion;

const ALL_EVENTS = [
    PaymentStateReceived::class,
    PaymentSucceeded::class,
    PaymentFailed::class,
    PaymentPartiallySucceeded::class,
    PaymentReserved::class,
    PaymentAuthorized::class,
    PaymentRefunded::class,
];

beforeEach(function (): void {
    Event::fake(ALL_EVENTS);
});

it('registers the callback route', function (): void {
    expect(route(LaravelBarion::CALLBACK_ROUTE))->toBe(url('barion/callback'));
});

it('verifies the payment state and dispatches the succeeded event', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse('Succeeded'))]);

    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertOk();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.test.barion.com/v4/Payment/'.PAYMENT_ID.'/PaymentState');
    Event::assertDispatched(PaymentStateReceived::class, fn (PaymentStateReceived $event): bool => $event->paymentState->paymentId === PAYMENT_ID);
    Event::assertDispatched(PaymentSucceeded::class, fn (PaymentSucceeded $event): bool => $event->paymentState->status === PaymentStatus::Succeeded);
    Event::assertNotDispatched(PaymentFailed::class);
});

it('only trusts the PaymentState API, never the callback payload', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse('Canceled'))]);

    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID, 'Status' => 'Succeeded', 'Total' => 999999])->assertOk();

    Event::assertDispatched(PaymentFailed::class, fn (PaymentFailed $event): bool => $event->paymentState->status === PaymentStatus::Canceled
        && $event->paymentState->total === 1500.0);
    Event::assertNotDispatched(PaymentSucceeded::class);
});

it('accepts the paymentId in the query string', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse('Prepared'))]);

    $this->get('/barion/callback?paymentId='.PAYMENT_ID)->assertOk();

    Event::assertDispatched(PaymentStateReceived::class);
    Event::assertNotDispatched(PaymentSucceeded::class);
    Event::assertNotDispatched(PaymentFailed::class);
});

it('does not require a CSRF token', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse())]);

    $this->call('POST', '/barion/callback', ['paymentId' => PAYMENT_ID])->assertOk();
});

it('rejects a missing or malformed paymentId without calling Barion', function (array $payload): void {
    Http::fake();

    $this->post('/barion/callback', $payload)->assertStatus(400);

    Http::assertNothingSent();
    Event::assertNothingDispatched();
})->with([
    'missing' => [[]],
    'not a guid' => [['paymentId' => '../../v2/Payment/Refund']],
    'array' => [['paymentId' => [PAYMENT_ID]]],
]);

it('answers with an error when the state cannot be verified so Barion retries', function (): void {
    Http::fake(['*' => Http::response(['Errors' => [['Title' => 'Unavailable']]], 503)]);

    $this->withoutExceptionHandling();

    expect(fn () => $this->post('/barion/callback', ['paymentId' => PAYMENT_ID]))->toThrow(Exception::class);

    Event::assertNothingDispatched();
});

it('can disable the callback route', function (): void {
    config()->set('laravel-barion.callback.enabled', false);
    Route::setRoutes(new RouteCollection);
    require __DIR__.'/../../routes/laravel-barion.php';

    expect(Route::has(LaravelBarion::CALLBACK_ROUTE))->toBeFalse();
});

it('can be handled manually from the app', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse('Expired'))]);

    $state = app(LaravelBarion::class)->handleCallback(PAYMENT_ID);

    expect($state->status)->toBe(PaymentStatus::Expired);
    Event::assertDispatched(PaymentFailed::class);
});

it('answers an unknown payment id quietly with 404', function (): void {
    Http::fake(['*' => Http::response(['Errors' => [[
        'Title' => 'The given payment id is invalid',
        'ErrorCode' => 'NotExistingPaymentId',
    ]]], 400)]);

    $this->withoutExceptionHandling();

    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertNotFound();

    Http::assertSentCount(1);
    Event::assertNothingDispatched();
});

it('still fails loudly on authentication errors', function (): void {
    Http::fake(['*' => Http::response(['Errors' => [['ErrorCode' => 'AuthenticationFailed', 'Title' => 'User authentication failed.']]], 401)]);

    $this->withoutExceptionHandling();

    expect(fn () => $this->post('/barion/callback', ['paymentId' => PAYMENT_ID]))
        ->toThrow(BarionApiException::class, 'AuthenticationFailed');
});

it('rate limits the callback route per IP address and payment', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse())]);
    config()->set('laravel-barion.callback.rate_limit', 2);

    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertOk();
    $this->post('/barion/callback', ['PaymentId' => strtoupper(PAYMENT_ID)])->assertOk();
    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertTooManyRequests();

    Http::assertSentCount(2);
});

it('does not throttle callbacks of other payments from the same IP address', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse())]);
    config()->set('laravel-barion.callback.rate_limit', 1);

    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertOk();
    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertTooManyRequests();
    $this->post('/barion/callback', ['paymentId' => 'aaaaaaaabbbbccccddddeeeeeeeeeeee'])->assertOk();
    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID], ['REMOTE_ADDR' => '10.0.0.2'])->assertOk();

    Http::assertSentCount(3);
});

it('rate limits the callback route per IP address across payments', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse())]);
    config()->set('laravel-barion.callback.rate_limit_per_ip', 2);

    $this->post('/barion/callback', ['paymentId' => 'aaaaaaaabbbbccccddddeeeeeeeeee01'])->assertOk();
    $this->post('/barion/callback', ['paymentId' => 'aaaaaaaabbbbccccddddeeeeeeeeee02'])->assertOk();
    $this->post('/barion/callback', ['paymentId' => 'aaaaaaaabbbbccccddddeeeeeeeeee03'])->assertTooManyRequests();
    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID], ['REMOTE_ADDR' => '10.0.0.2'])->assertOk();

    Http::assertSentCount(3);
});

it('can disable the per IP callback rate limit only', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse())]);
    config()->set('laravel-barion.callback.rate_limit', 1);
    config()->set('laravel-barion.callback.rate_limit_per_ip', null);

    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertOk();
    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertTooManyRequests();
    $this->post('/barion/callback', ['paymentId' => 'aaaaaaaabbbbccccddddeeeeeeeeeeee'])->assertOk();
});

it('can disable the callback rate limit', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse())]);
    config()->set('laravel-barion.callback.rate_limit', null);
    config()->set('laravel-barion.callback.rate_limit_per_ip', null);

    foreach (range(1, 5) as $attempt) {
        $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertOk();
    }
});

it('dispatches the event of the verified status', function (string $status, array $expected): void {
    Http::fake(['*' => Http::response(paymentStateResponse($status))]);

    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertOk();

    foreach (ALL_EVENTS as $event) {
        in_array($event, [PaymentStateReceived::class, ...$expected], true)
            ? Event::assertDispatched($event, 1)
            : Event::assertNotDispatched($event);
    }
})->with([
    'prepared' => ['Prepared', []],
    'started' => ['Started', []],
    'reserved' => ['Reserved', [PaymentReserved::class]],
    'authorized' => ['Authorized', [PaymentAuthorized::class]],
    'succeeded' => ['Succeeded', [PaymentSucceeded::class]],
    'partially succeeded' => ['PartiallySucceeded', [PaymentPartiallySucceeded::class]],
    'canceled' => ['Canceled', [PaymentFailed::class]],
    'failed' => ['Failed', [PaymentFailed::class]],
    'expired' => ['Expired', [PaymentFailed::class]],
]);

it('dispatches the refunded event next to the status event', function (): void {
    Http::fake(['*' => Http::response(refundedPaymentStateResponse())]);

    $this->post('/barion/callback', ['paymentId' => PAYMENT_ID])->assertOk();

    Event::assertDispatched(PaymentSucceeded::class, 1);
    Event::assertDispatched(PaymentRefunded::class, function (PaymentRefunded $event): bool {
        return $event->paymentState->refundedAmount() === 500.0
            && array_map(fn (TransactionDetailData $transaction): ?string => $transaction->transactionId, $event->paymentState->refundTransactions()) === ['trx-refund-1'];
    });
});
