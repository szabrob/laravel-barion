---
name: laravel-barion-development
description: >
  Integrate Barion Smart Gateway payments into a Laravel application with the szabrob/laravel-barion
  package: starting payments, verified callbacks and events, payment state, refunds, reservations,
  delayed capture and the Barion Pixel, all through spatie/laravel-data DTOs.
license: MIT
metadata:
  author: Szabó Róbert
---

# Laravel Barion

Use this skill when a Laravel application takes payments through Barion with `szabrob/laravel-barion`.

## Primary Goal

- start, verify and settle Barion payments through the package's DTO-based API, and let order state change only from the verified PaymentState result

## Workflow

### 1. Install and configure

- `composer require szabrob/laravel-barion`, optionally `php artisan vendor:publish --tag="laravel-barion-config"`
- set `BARION_ENVIRONMENT` (`test` or `production`), and the POS key and payee of that environment: `BARION_TEST_POS_KEY` / `BARION_TEST_PAYEE` or `BARION_POS_KEY` / `BARION_PAYEE`
- the test shop and the live shop have separate POS keys. Never copy one into the other's slot.

### 2. Start the payment

- create a new payment attempt for every "Pay" click, and use it in `paymentRequestId` and `posTransactionId` (e.g. `order-1001-2`). The same request returns the same Barion payment, so reusing the id after a cancel or expiry brings back the old payment.
- build `PaymentRequestData` with `PaymentTransactionData` and `ItemData` (namespace `LaravelBarion\LaravelBarion\Data\...`)
- call `LaravelBarion::startPayment($request)`, store `$response->paymentId` on the order, and redirect to `$response->gatewayUrl`
- `payee` defaults to the configured payee, and `callbackUrl` defaults to the package route `laravel-barion.callback`

### 3. React to the verified state

- listen to `LaravelBarion\LaravelBarion\Events\PaymentSucceeded`, `PaymentFailed` or `PaymentStateReceived`. Each has `->paymentState` (`PaymentStateData`) from the v4 PaymentState API.
- look up the order by `paymentState->paymentId` and keep the listener idempotent, because callbacks repeat
- on the return page, call `LaravelBarion::paymentState($paymentId)`. Don't trust query parameters.

### 4. Settle when needed

- `LaravelBarion::refund(RefundRequestData)` with a unique `posTransactionId` for each refund
- `LaravelBarion::finishReservation(FinishReservationRequestData)` for `PaymentType::Reservation`
- `LaravelBarion::capture(CaptureRequestData)` and `cancelAuthorization(CancelAuthorizationRequestData)` for `PaymentType::DelayedCapture`

## Rules, References, and Templates

- auth uses the `x-pos-key` header, set by the package. Never add `POSKey` to request bodies.
- idempotency keys are sent automatically and derived from the request. Pass `idempotencyKey:` only when you need your own key.
- errors: `BarionApiException` (`status`, `errors`, `hasErrorCode()`), `BarionConnectionException`, `BarionResponseException` (unexpected response shape, has the raw `body`), and `BarionException` for configuration problems
- the callback route is rate limited per IP (`callback.rate_limit`, default 120 per minute). Unknown payment ids get a 404.
- hide the `x-pos-key` header in Telescope and other HTTP request loggers
- Barion Pixel: `<x-laravel-barion::pixel />` in the layout `<head>`. It needs `BARION_PIXEL_ID` and accepts an optional `:nonce`.
- tests: fake `api.test.barion.com/*` with `Http::fake()` and post to `/barion/callback`
- full docs: the package `README.md`

## Examples

```php
$payment = LaravelBarion::startPayment(new PaymentRequestData(
    paymentRequestId: "order-{$order->id}-{$attempt->id}",
    transactions: [new PaymentTransactionData(
        posTransactionId: "order-{$order->id}-{$attempt->id}",
        total: $order->total,
        items: $order->lines->map(fn ($line) => new ItemData(
            name: $line->name, description: $line->name, quantity: $line->qty,
            unit: 'pcs', unitPrice: $line->price, itemTotal: $line->total, sku: $line->sku,
        ))->all(),
    )],
    redirectUrl: route('checkout.barion.return', $order),
));

return redirect()->away($payment->gatewayUrl);
```

## Anti-patterns

- marking an order paid from the callback request or the redirect query string instead of `PaymentStateData`
- calling the deprecated v2 `GetPaymentState` endpoint or building Barion HTTP calls by hand
- reusing the same `posTransactionId` for different refunds
- reusing a `paymentRequestId` for a new payment attempt (e.g. `"order-{$order->id}"` alone)
- assuming a payment event arrives exactly once
