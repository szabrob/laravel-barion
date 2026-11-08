<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use LaravelBarion\LaravelBarion\Enums\PaymentStatus;
use LaravelBarion\LaravelBarion\Events\PaymentFailed;
use LaravelBarion\LaravelBarion\Events\PaymentStateReceived;
use LaravelBarion\LaravelBarion\Events\PaymentSucceeded;
use LaravelBarion\LaravelBarion\LaravelBarion;

beforeEach(function (): void {
    Event::fake([PaymentStateReceived::class, PaymentSucceeded::class, PaymentFailed::class]);
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
