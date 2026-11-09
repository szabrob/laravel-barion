<div align="center">
    <h1>Laravel Barion</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/szabrob/laravel-barion"><img src="https://img.shields.io/packagist/v/szabrob/laravel-barion.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/szabrob/laravel-barion"><img src="https://img.shields.io/packagist/php-v/szabrob/laravel-barion.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/szabrob/laravel-barion"><img src="https://badge.laravel.cloud/badge/szabrob/laravel-barion?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/szabrob/laravel-barion/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/szabrob/laravel-barion/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/szabrob/laravel-barion"><img src="https://img.shields.io/packagist/dt/szabrob/laravel-barion.svg?style=flat-square" alt="Total Downloads"></a>
</p>

Barion Smart Gateway integration for Laravel with typed DTOs, callback events and Barion Pixel support.

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- [spatie/laravel-data](https://spatie.be/docs/laravel-data) 4 (installed automatically)

## Installation

```bash
composer require szabrob/laravel-barion
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-barion-config"
```

You may also publish the views (the Barion Pixel component) with the `laravel-barion-views` tag, or everything at once with `laravel-barion`.

## Configuration

```dotenv
BARION_ENVIRONMENT=test            # test | production

BARION_TEST_POS_KEY=your-test-shop-pos-key
BARION_TEST_PAYEE=test-shop-wallet@example.com

BARION_POS_KEY=your-live-shop-pos-key
BARION_PAYEE=live-shop-wallet@example.com

BARION_REDIRECT_URL=https://example.com/checkout/barion/return
BARION_PIXEL_ID=BP-0000000000-00
```

| Environment  | API                           | Gateway                              |
|--------------|-------------------------------|--------------------------------------|
| `test`       | `https://api.test.barion.com` | `https://secure.test.barion.com/Pay` |
| `production` | `https://api.barion.com`      | `https://secure.barion.com/Pay`      |

The test shop and the live shop have different POS keys. The package always reads the key of the active environment's section, and it never falls back to the other one: a missing key throws a `BarionException` before any request is made.

### How requests are sent

- **Authentication**: the POS key goes in the `x-pos-key` header. It is never put in the request body or the query string.
- **Payment state**: the package uses the v4 `GET /v4/Payment/{paymentId}/PaymentState` endpoint. The deprecated v2 `GetPaymentState` endpoint is not used.
- **Idempotency**: every POST call sends an `Idempotency-Key` header. If you don't set one, the package derives it from the environment, the endpoint and the request body. Sending the same request again, for example from a re-queued job, reuses the same key. Barion then returns the original result instead of running the operation again. Pass `idempotencyKey:` on any request DTO to use your own key.
- **Amounts**: money fields (`Total`, `UnitPrice`, `ItemTotal`, `AmountToRefund`) are rounded to 2 decimals before sending. Other numbers lose their float noise, so `0.1 + 0.2` is sent as `0.3`.
- **Retries**: connection errors and 5xx responses are retried with the same idempotency key (`http.retry` config). 4xx responses are not retried.

## Usage

The package and your application talk through [spatie/laravel-data](https://spatie.be/docs/laravel-data) DTOs. You build request DTOs with camelCase properties, and the package maps them to the Barion field names. Responses come back as typed DTOs with enums and `CarbonImmutable` dates.

### Starting a payment

```php
use Szabrob\LaravelBarion\Data\ItemData;
use Szabrob\LaravelBarion\Data\Requests\PaymentRequestData;
use Szabrob\LaravelBarion\Data\Requests\PaymentTransactionData;
use Szabrob\LaravelBarion\Enums\Currency;
use Szabrob\LaravelBarion\Facades\Barion;

$attempt = $order->paymentAttempts()->create(); // a new attempt every time the customer clicks "Pay"

$payment = Barion::startPayment(new PaymentRequestData(
    paymentRequestId: "order-{$order->id}-{$attempt->id}",
    transactions: [
        new PaymentTransactionData(
            posTransactionId: "order-{$order->id}-{$attempt->id}",
            total: 1500,
            items: [
                new ItemData(name: 'T-shirt', description: 'Black, M', quantity: 1, unit: 'pcs', unitPrice: 1500, itemTotal: 1500, sku: 'TS-BLK-M'),
            ],
            // payee: defaults to the configured payee of the active environment
        ),
    ],
    currency: Currency::HUF,
    redirectUrl: route('checkout.barion.return', $order),
    orderNumber: (string) $order->id,
    payerHint: $order->email,
    // callbackUrl: defaults to the package callback route
));

$order->update(['barion_payment_id' => $payment->paymentId]);

return redirect()->away($payment->gatewayUrl);
```

> **Use a new `paymentRequestId` for every payment attempt.** Because the idempotency key comes from the request body, starting the same request again returns the *same* Barion payment. That protects against duplicate payments on retries. But if the customer canceled the payment or let it expire, a "pay again" with the same `paymentRequestId` and the same data would bring back the old, closed payment. Include an attempt number (as above) or another per-attempt value in `paymentRequestId` and `posTransactionId`.

`PaymentRequestData::from([...])` with a camelCase array works too. Optional 3D Secure data (`shippingAddress`, `billingAddress`, `payerAccountInformation`, `purchaseInformation`, `challengePreference`) and token payment fields (`initiateRecurrence`, `recurrenceId`, `recurrenceType`, `traceId`) are supported, see [Token payments](#token-payments-saved-cards). For `PaymentType::Reservation` and `PaymentType::DelayedCapture`, also set `reservationPeriod` or `delayedCapturePeriod`.

### Callback and events

Barion calls the callback URL with only the `paymentId`, and the call is not signed. The package registers `GET|POST /barion/callback` (route name `laravel-barion.callback`, outside the `web` group, so it needs no CSRF token). For every call it:

1. checks that `paymentId` looks like a Barion payment id (`400` otherwise, without calling Barion),
2. queries the **v4 PaymentState API**. No other value from the callback request is used,
3. dispatches events with the `PaymentStateData` it got back,
4. answers `200`. If Barion does not know the payment (`NotExistingPaymentId`), it answers `404` without logging an error. For any other failure (for example a wrong POS key) it answers with an error, so Barion sends the callback again later.

Every callback makes an outgoing API call, so the route is rate limited per IP address and `paymentId`: `callback.rate_limit` requests per minute for one payment from one IP, `120` by default, `null` disables it. Throttled requests get a `429`, and Barion retries them.

| Event                  | When                                                                 |
|------------------------|----------------------------------------------------------------------|
| `PaymentStateReceived` | Every callback                                                       |
| `PaymentSucceeded`     | Verified status is `Succeeded`                                       |
| `PaymentFailed`        | Verified status is `Canceled`, `Failed` or `Expired`                 |
| `PaymentPartiallySucceeded` | Verified status is `PartiallySucceeded` (Reservation payment finished with only part of its transactions or amount) |
| `PaymentReserved`      | Verified status is `Reserved` (Reservation payment, call `finishReservation()`) |
| `PaymentAuthorized`    | Verified status is `Authorized` (DelayedCapture payment, call `capture()` or `cancelAuthorization()`) |
| `PaymentRefunded`      | The payment has succeeded refund transactions. It is dispatched in addition to the status event. |

Every event has a `paymentState` property (`PaymentStateData`). A refund does not change the payment status: the payment stays `Succeeded`, its `total` drops, and a refund transaction appears. Use `$paymentState->refundTransactions()` (succeeded `Refund`, `RefundToBankCard` and `RefundToBankAccount` transactions) and `$paymentState->refundedAmount()`. Every later callback of the same payment dispatches `PaymentRefunded` again, so deduplicate refunds by their `transactionId`:

```php
use Szabrob\LaravelBarion\Events\PaymentRefunded;

Event::listen(function (PaymentRefunded $event): void {
    foreach ($event->paymentState->refundTransactions() as $refund) {
        Refund::firstOrCreate(
            ['barion_transaction_id' => $refund->transactionId],
            ['amount' => $refund->total],
        );
    }
});
```

```php
use Illuminate\Support\Facades\Event;
use Szabrob\LaravelBarion\Events\PaymentSucceeded;

Event::listen(function (PaymentSucceeded $event): void {
    $state = $event->paymentState;

    $order = Order::where('barion_payment_id', $state->paymentId)->firstOrFail();

    if ($order->isPaid()) {
        return; // callbacks can repeat (e.g. after a refund): keep listeners idempotent
    }

    $order->markAsPaid($state->total, $state->completedAt);
});
```

Barion may send the callback more than once for the same payment, for example after a refund. Write your listeners so that running them twice is safe.

Set the path and middleware in `laravel-barion.callback`. To handle callbacks in your own controller instead, set `callback.enabled` to `false` and call `Barion::handleCallback($paymentId)` there. It queries the state and dispatches the same events. On the redirect (return) page, show the state from `Barion::paymentState($paymentId)`. Don't trust query parameters there either.

### Payment state

```php
$state = Barion::paymentState($paymentId); // PaymentStateData

$state->status;           // PaymentStatus enum (isSuccessful(), isFailed(), isFinal())
$state->total;            // float
$state->currency;         // Currency enum
$state->completedAt;      // ?CarbonImmutable
$state->transactions;     // array<TransactionDetailData>
$state->fundingInformation?->bankCard?->maskedPan;
$state->recurrenceType;   // ?RecurrenceType of a token payment
$state->traceId;          // ?string, store it from the first token payment
```

### Refund

```php
use Szabrob\LaravelBarion\Data\Requests\RefundRequestData;
use Szabrob\LaravelBarion\Data\Requests\TransactionToRefundData;

$refund = Barion::refund(new RefundRequestData(
    paymentId: $paymentId,
    transactionsToRefund: [
        new TransactionToRefundData(
            transactionId: $barionTransactionId,
            posTransactionId: "refund-{$refund->id}", // unique per refund
            amountToRefund: 500,
            comment: 'Returned item',
        ),
    ],
));
```

The default idempotency key is derived from the request body. Use a unique `posTransactionId` for each refund: two refunds with exactly the same body count as the same request.

### Reservation and delayed capture

```php
use Szabrob\LaravelBarion\Data\Requests\CancelAuthorizationRequestData;
use Szabrob\LaravelBarion\Data\Requests\CaptureRequestData;
use Szabrob\LaravelBarion\Data\Requests\FinishReservationRequestData;
use Szabrob\LaravelBarion\Data\Requests\TransactionToFinishData;

// PaymentType::Reservation
Barion::finishReservation(new FinishReservationRequestData($paymentId, [
    new TransactionToFinishData(transactionId: $barionTransactionId, total: 1200),
]));

// PaymentType::DelayedCapture
Barion::capture(new CaptureRequestData($paymentId, [
    new TransactionToFinishData(transactionId: $barionTransactionId, total: 1500),
]));

Barion::cancelAuthorization(new CancelAuthorizationRequestData($paymentId));
```

All three return a `PaymentOperationResponseData` (`isSuccessful`, `status`, `transactions`).

### Token payments (saved cards)

A token payment charges a card the customer saved with an earlier payment, without sending them to the Barion gateway again. You create the token: a `recurrenceId` of your own, unique for every registration, sent with the first payment. Barion links the card (or Barion balance) the customer pays with to it. Token payments are a premium feature: until Barion enables it for your shop, `startPayment()` fails with `RecurringPaymentNotAllowed`.

`recurrenceType` tells Barion how the token will be used:

| `RecurrenceType`           | Who starts the charge      | Rules for later charges                                                                                          | Use it for |
|----------------------------|----------------------------|------------------------------------------------------------------------------------------------------------------|------------|
| `OneClickPayment`          | The customer, on your site | Every charge needs 3D Secure authentication, done on your site ([step 3](#3-oneclickpayment-3d-secure-on-your-site-then-completepayment)). | "Pay with the saved card" at checkout |
| `MerchantInitiatedPayment` | You, without the customer  | Any amount, any time. No 3D Secure, but the liability for fraud is yours.                                       | Usage based bills, top-ups, a subscription with a free first period |
| `RecurringPayment`         | You, on a fixed schedule   | The amount must not be higher than the first payment, at least `recurringFrequency` days must pass between charges, and no charge after `recurringExpiry`. Barion does not check these, the card issuer declines the charge. | Subscriptions with a fixed fee |

Use the same `recurrenceType` for the first payment and for every later charge of the token.

#### 1. Register the card with the first payment

The first payment is a normal payment on the Barion gateway, with `initiateRecurrence: true`. For `RecurringPayment`, also send `recurringExpiry` and `recurringFrequency` (days) in `purchaseInformation`:

```php
use Illuminate\Support\Str;
use Szabrob\LaravelBarion\Data\ThreeDSecure\PurchaseInformationData;
use Szabrob\LaravelBarion\Enums\RecurrenceType;

$subscription->update(['barion_recurrence_id' => $recurrenceId = (string) Str::uuid()]);

$payment = Barion::startPayment(new PaymentRequestData(
    paymentRequestId: "sub-{$subscription->id}-first",
    transactions: [/* the first period, as in "Starting a payment" */],
    redirectUrl: route('subscriptions.barion.return', $subscription),
    payerHint: $subscription->user->email,
    initiateRecurrence: true,
    recurrenceId: $recurrenceId,
    recurrenceType: RecurrenceType::RecurringPayment,
    purchaseInformation: new PurchaseInformationData(
        recurringExpiry: now()->addYears(2),
        recurringFrequency: 30,
    ),
));

$subscription->update(['barion_payment_id' => $payment->paymentId]);

return redirect()->away($payment->gatewayUrl);
```

When the payment succeeds, the token is ready. For `MerchantInitiatedPayment` and `RecurringPayment`, store the `traceId` too: every later charge must send it, or the card issuer can decline it. Barion only returns a `traceId` for bank card payments, and `OneClickPayment` does not use it.

```php
use Szabrob\LaravelBarion\Events\PaymentSucceeded;

Event::listen(function (PaymentSucceeded $event): void {
    $state = $event->paymentState;

    if ($state->recurrenceType === null) {
        return; // not a token payment
    }

    Subscription::query()
        ->where('barion_payment_id', $state->paymentId)
        ->whereNull('barion_trace_id')
        ->update(['barion_trace_id' => $state->traceId, 'next_charge_at' => now()->addDays(30)]);
});
```

`PaymentStateData` has `recurrenceType` and `traceId`. Barion does not return the `recurrenceId` in the PaymentState response, so keep using the one you stored. A `traceId` belongs to one token and one `recurrenceType`, so store it with both.

#### 2. Charge the token on a schedule

A later `MerchantInitiatedPayment` or `RecurringPayment` charge is a `startPayment()` call with `initiateRecurrence: false`, the stored `recurrenceId`, the same `recurrenceType` and the stored `traceId`. There is no redirect: Barion charges the card at once and answers with a `recurrenceResult`.

```php
// routes/console.php
use Illuminate\Support\Facades\Schedule;
use Szabrob\LaravelBarion\Enums\RecurrenceResult;

Schedule::call(function (): void {
    Subscription::query()->where('next_charge_at', '<=', now())->each(function (Subscription $subscription): void {
        $period = $subscription->next_charge_at->format('Y-m-d');

        $response = Barion::startPayment(new PaymentRequestData(
            // One id per billing period: a retry in the same period returns the same payment instead of charging twice.
            paymentRequestId: "sub-{$subscription->id}-{$period}",
            transactions: [
                new PaymentTransactionData(
                    posTransactionId: "sub-{$subscription->id}-{$period}",
                    total: $subscription->price, // RecurringPayment: not more than the first payment
                    items: [new ItemData(name: 'Monthly plan', description: $period, quantity: 1, unit: 'month', unitPrice: $subscription->price, itemTotal: $subscription->price)],
                ),
            ],
            initiateRecurrence: false,
            recurrenceId: $subscription->barion_recurrence_id,
            recurrenceType: RecurrenceType::RecurringPayment,
            traceId: $subscription->barion_trace_id,
        ));

        match ($response->recurrenceResult) {
            RecurrenceResult::Successful => $subscription->update(['next_charge_at' => $subscription->next_charge_at->addDays(30)]),
            // For MerchantInitiatedPayment and RecurringPayment this means the traceId is missing or wrong.
            RecurrenceResult::ThreeDSAuthenticationRequired => $subscription->markTraceIdMissing(),
            default => $subscription->markChargeFailed(), // e.g. declined or expired card: retry later or ask for a new card
        };
    });
})->hourly();
```

`Successful` means Barion accepted the charge. Fulfil the period from the callback events (`PaymentSucceeded`) as with any other payment. Token-specific failures come back as a `BarionApiException`. Check them with `hasErrorCode()`, for example `InvalidRecurrenceId`, `CardExpired` (register a new card), `ThreeDsNotEnabled` or `InvalidTraceId` (check the `traceId`), and `TooManyUnsuccessfulRecurringPaymentAttempts` (5 failures block the token until the next day).

#### 3. OneClickPayment: 3D Secure on your site, then completePayment()

A `OneClickPayment` charge (`initiateRecurrence: false`, the stored `recurrenceId`, no `traceId`) comes back with `status` `Prepared`, `recurrenceResult` `ThreeDSAuthenticationRequired` and `threeDSAuthClientData`. The customer authenticates on your page with Barion's off-site library, and then your backend completes the payment. That call does the actual charge:

```html
<!-- secure.test.barion.com in the test environment -->
<link href="https://secure.barion.com/static/barion.offsitegw.min.css" rel="stylesheet">
<script src="https://secure.barion.com/static/barion.offsitegw.min.js"></script>
<script>
    const gw = new Barion.OffsiteGw({
        onAuthenticationSucceeded: () => fetch('/checkout/complete', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } }),
        onAuthenticationFailed: () => showError('The authentication failed, no charge was made.'),
    });
    document.addEventListener('DOMContentLoaded', () => gw.init());

    // threeDSAuthClientData from the startPayment() response, passed on by your backend
    gw.authenticate(threeDSAuthClientData);
</script>
```

```php
use Szabrob\LaravelBarion\Data\Requests\CompletePaymentRequestData;

// POST /checkout/complete
$response = Barion::completePayment(new CompletePaymentRequestData($order->barion_payment_id));

$response->status;        // PaymentStatus
$response->isSuccessful;
```

`completePayment()` returns a `CompletePaymentResponseData` (`paymentId`, `status`, `isSuccessful`, `paymentRequestId`, `traceId`). Without a finished authentication, Barion answers `PaymentStatusNotValid` ("The 3DS authentication is not completed"). Payment/Complete is the only endpoint that needs the POS key in the request body as well. The package adds it, so don't put it in yourself. The callback still arrives, so fulfil the order from the callback events as usual.

> **Testing:** the Barion test cards always get a frictionless 3D Secure flow, so you never see a challenge with them. A challenge only appears with a real card. On the test environment it is not charged, but the payment fails at the end.

### Errors

| Exception                   | When                                                                 |
|-----------------------------|----------------------------------------------------------------------|
| `BarionApiException`        | Barion answered with an error status or a non-empty `Errors` list. It has `status`, `errors` (`ApiErrorData[]`) and the raw `body`. |
| `BarionConnectionException` | Barion could not be reached, even after the retries.                 |
| `BarionResponseException`   | Barion answered successfully, but the response does not fit the expected DTO, for example a required field is missing. It has `dataClass` and the raw `body`. |
| `BarionException`           | Configuration problem (unknown environment, missing POS key or payee). All the exceptions above extend this class. |

`BarionApiException::hasErrorCode('NotExistingPaymentId')` checks for a specific Barion error code.

### Keeping the POS key out of logs

The POS key is sent in the `x-pos-key` request header. Tools that record outgoing HTTP requests (Telescope, Nightwatch, custom HTTP client logging) also store the headers, unless you tell them not to. For Telescope:

```php
// App\Providers\TelescopeServiceProvider::hideSensitiveRequestDetails()
Telescope::hideRequestHeaders(['x-pos-key']);
```

### Barion Pixel

Place the component in your layout's `<head>`. It renders nothing when `BARION_PIXEL_ID` is not set:

```blade
<x-laravel-barion::pixel />

{{-- with a Content Security Policy nonce --}}
<x-laravel-barion::pixel :nonce="Vite::cspNonce()" />
```

### Testing your application

The package uses the Laravel HTTP client, so you can fake Barion with `Http::fake()`:

```php
Http::fake([
    'api.test.barion.com/v4/Payment/*/PaymentState' => Http::response([
        'PaymentId' => $paymentId,
        'Status' => 'Succeeded',
        'Total' => 1500,
        'Currency' => 'HUF',
    ]),
]);

$this->post('/barion/callback', ['paymentId' => $paymentId])->assertOk();
```

## Workbench (manual testing)

The repository has a workbench test UI for trying the package against the real Barion test API:

1. Fill in the Barion variables in `workbench/.env` (copied from `workbench/.env.example`, git-ignored).
2. Run `composer serve` and open `http://localhost:8000`.
3. Start a payment, pay on the Barion test gateway, then come back to the payment page. There you can query the PaymentState, refund, finish a reservation, capture or cancel an authorization.
4. For token payments, pick a recurrence type under **Kártya regisztrálása** on the start form. After the payment succeeds, set up a scheduled charge on its payment page. `vendor/bin/testbench schedule:work` runs `barion:charge-due` every minute. You can also run it once with `vendor/bin/testbench barion:charge-due`, or use the **Terhelés most** button.

Barion cannot reach `localhost`. For real callbacks, expose the server through a tunnel (for example `ngrok http 8000`) and set `APP_URL` to the public URL. The workbench generates the RedirectUrl and CallbackUrl from `APP_URL`. Without a tunnel, use the **Callback szimuláció** button. It runs the same `handleCallback()` flow.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Laravel Barion! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Szabó Róbert](https://github.com/szabrob)
- [All Contributors](../../contributors)

## License

Laravel Barion is open-sourced software licensed under the [MIT license](LICENSE.md).
