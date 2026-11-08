<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBarion\Data\ItemData;
use Szabrob\LaravelBarion\Data\Requests\PaymentRequestData;
use Szabrob\LaravelBarion\Data\Requests\PaymentTransactionData;
use Szabrob\LaravelBarion\Data\ThreeDSecure\AddressData;
use Szabrob\LaravelBarion\Data\ThreeDSecure\GiftCardPurchaseData;
use Szabrob\LaravelBarion\Data\ThreeDSecure\PayerAccountInformationData;
use Szabrob\LaravelBarion\Data\ThreeDSecure\PurchaseInformationData;
use Szabrob\LaravelBarion\Enums\Currency;
use Szabrob\LaravelBarion\Enums\FundingSource;
use Szabrob\LaravelBarion\Enums\PaymentStatus;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\AccountCreationIndicator;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\ChallengePreference;
use Szabrob\LaravelBarion\Enums\ThreeDSecure\PurchaseType;
use Szabrob\LaravelBarion\Enums\TransactionStatus;
use Szabrob\LaravelBarion\Exceptions\BarionException;
use Szabrob\LaravelBarion\Exceptions\BarionResponseException;
use Szabrob\LaravelBarion\Facades\Barion;

function fakeStart(): void
{
    Http::fake([
        'api.test.barion.com/v2/Payment/Start' => Http::response([
            'PaymentId' => PAYMENT_ID,
            'PaymentRequestId' => 'order-1001',
            'Status' => 'Prepared',
            'QRUrl' => 'https://api.test.barion.com/qr/generate?paymentId='.PAYMENT_ID,
            'RecurrenceResult' => 'None',
            'ThreeDSAuthClientData' => null,
            'Transactions' => [[
                'POSTransactionId' => 'order-1001-t1',
                'TransactionId' => 'trx-1',
                'Status' => 'Prepared',
                'TransactionTime' => '2026-11-08T10:00:00Z',
                'RelatedId' => null,
            ]],
            'Errors' => [],
        ]),
        'api.barion.com/v2/Payment/Start' => Http::response([
            'PaymentId' => PAYMENT_ID,
            'Status' => 'Prepared',
            'Errors' => [],
        ]),
    ]);
}

it('starts a payment on the test API with the POS key in the x-pos-key header only', function (): void {
    fakeStart();

    Barion::startPayment(paymentRequest());

    Http::assertSent(function (Request $request): bool {
        expect($request->url())->toBe('https://api.test.barion.com/v2/Payment/Start')
            ->and($request->method())->toBe('POST')
            ->and($request->header('x-pos-key'))->toBe(['test-pos-key'])
            ->and($request->body())->not->toContain('test-pos-key')
            ->and($request->data())->not->toHaveKey('POSKey');

        return true;
    });
});

it('maps the request DTO to the Barion request body', function (): void {
    fakeStart();

    Barion::startPayment(paymentRequest());

    Http::assertSent(function (Request $request): bool {
        expect($request->data())->toBe([
            'PaymentRequestId' => 'order-1001',
            'Transactions' => [[
                'POSTransactionId' => 'order-1001-t1',
                'Total' => 1500.0,
                'Items' => [[
                    'Name' => 'T-shirt',
                    'Description' => 'Black, M',
                    'Quantity' => 1.0,
                    'Unit' => 'pcs',
                    'UnitPrice' => 1500.0,
                    'ItemTotal' => 1500.0,
                    'SKU' => 'TS-BLK-M',
                ]],
                'Payee' => 'test-payee@example.com',
                'PayeeTransactions' => [],
            ]],
            'Currency' => 'HUF',
            'Locale' => 'hu-HU',
            'PaymentType' => 'Immediate',
            'GuestCheckOut' => true,
            'FundingSources' => ['All'],
            'PaymentWindow' => '00:30:00',
            'RedirectUrl' => 'https://shop.test/orders/1001',
            'CallbackUrl' => url('barion/callback'),
            'InitiateRecurrence' => false,
        ]);

        return true;
    });
});

it('keeps an explicit payee and callback url', function (): void {
    fakeStart();

    $request = new PaymentRequestData(
        paymentRequestId: 'order-1001',
        transactions: paymentRequest(payee: 'other@example.com')->transactions,
        fundingSources: [FundingSource::BankCard, FundingSource::GooglePay],
        callbackUrl: 'https://shop.test/custom-callback',
    );

    Barion::startPayment($request);

    Http::assertSent(function (Request $request): bool {
        expect($request['Transactions'][0]['Payee'])->toBe('other@example.com')
            ->and($request['CallbackUrl'])->toBe('https://shop.test/custom-callback')
            ->and($request['FundingSources'])->toBe(['Bankcard', 'GooglePay']);

        return true;
    });
});

it('omits the callback url when the package callback route is disabled', function (): void {
    fakeStart();
    config()->set('laravel-barion.callback.enabled', false);

    Barion::startPayment(paymentRequest());

    Http::assertSent(fn (Request $request): bool => ! array_key_exists('CallbackUrl', $request->data()));
});

it('returns a typed response with the gateway url', function (): void {
    fakeStart();

    $response = Barion::startPayment(paymentRequest());

    expect($response->paymentId)->toBe(PAYMENT_ID)
        ->and($response->status)->toBe(PaymentStatus::Prepared)
        ->and($response->gatewayUrl)->toBe('https://secure.test.barion.com/Pay?id='.PAYMENT_ID)
        ->and($response->qrUrl)->toStartWith('https://api.test.barion.com/qr')
        ->and($response->transactions)->toHaveCount(1)
        ->and($response->transactions[0]->posTransactionId)->toBe('order-1001-t1')
        ->and($response->transactions[0]->status)->toBe(TransactionStatus::Prepared)
        ->and($response->transactions[0]->transactionTime?->toIso8601String())->toBe('2026-11-08T10:00:00+00:00');
});

it('uses the production API, gateway, POS key and payee in the production environment', function (): void {
    fakeStart();
    config()->set('laravel-barion.environment', 'production');

    $response = Barion::startPayment(paymentRequest());

    expect($response->gatewayUrl)->toBe('https://secure.barion.com/Pay?id='.PAYMENT_ID);

    Http::assertSent(function (Request $request): bool {
        expect($request->url())->toBe('https://api.barion.com/v2/Payment/Start')
            ->and($request->header('x-pos-key'))->toBe(['live-pos-key'])
            ->and($request['Transactions'][0]['Payee'])->toBe('live-payee@example.com');

        return true;
    });
});

it('never falls back to the POS key of the other environment', function (): void {
    Http::fake();
    config()->set('laravel-barion.environment', 'production');
    config()->set('laravel-barion.production.pos_key', null);

    expect(fn () => Barion::startPayment(paymentRequest()))
        ->toThrow(BarionException::class, 'Missing Barion POS key for the [production] environment');

    Http::assertNothingSent();
});

it('rejects an unknown environment', function (): void {
    Http::fake();
    config()->set('laravel-barion.environment', 'staging');

    expect(fn () => Barion::startPayment(paymentRequest()))
        ->toThrow(BarionException::class, 'Invalid Barion environment [staging]');

    Http::assertNothingSent();
});

it('requires a payee', function (): void {
    Http::fake();
    config()->set('laravel-barion.test.payee', null);

    expect(fn () => Barion::startPayment(paymentRequest()))
        ->toThrow(BarionException::class, 'no default payee');

    Http::assertNothingSent();
});

it('serializes the 3D Secure data', function (): void {
    fakeStart();

    Barion::startPayment(new PaymentRequestData(
        paymentRequestId: 'order-1001',
        transactions: paymentRequest()->transactions,
        challengePreference: ChallengePreference::NoPreference,
        shippingAddress: new AddressData(country: 'HU', city: 'Budapest', zip: '1111', street: 'Fő utca 1.', fullName: 'Teszt Elek'),
        payerAccountInformation: new PayerAccountInformationData(
            accountCreated: CarbonImmutable::parse('2026-01-02 03:04:05', 'Europe/Budapest'),
            accountCreationIndicator: AccountCreationIndicator::MoreThan60Days,
        ),
        purchaseInformation: new PurchaseInformationData(
            purchaseType: PurchaseType::GoodsAndServicePurchase,
            giftCardPurchase: new GiftCardPurchaseData(amount: 5000, count: 1),
        ),
    ));

    Http::assertSent(function (Request $request): bool {
        expect($request['ChallengePreference'])->toBe('NoPreference')
            ->and($request['ShippingAddress'])->toBe(['Country' => 'HU', 'City' => 'Budapest', 'Zip' => '1111', 'Street' => 'Fő utca 1.', 'FullName' => 'Teszt Elek'])
            ->and($request['PayerAccountInformation'])->toBe(['AccountCreated' => '2026-01-02T02:04:05', 'AccountCreationIndicator' => 'MoreThan60Days'])
            ->and($request['PurchaseInformation'])->toBe(['GiftCardPurchase' => ['Amount' => 5000.0, 'Count' => 1], 'PurchaseType' => 'GoodsAndServicePurchase']);

        return true;
    });
});

it('rounds amounts and removes float noise before sending', function (): void {
    fakeStart();

    Barion::startPayment(new PaymentRequestData(
        paymentRequestId: 'order-1001',
        transactions: [new PaymentTransactionData(
            posTransactionId: 'order-1001-t1',
            total: 0.1 + 0.2,
            items: [new ItemData(name: 'Tea', description: 'Green', quantity: 0.1 + 0.2, unit: 'kg', unitPrice: 1, itemTotal: 0.30000000000000004)],
        )],
        currency: Currency::EUR,
    ));

    Http::assertSent(function (Request $request): bool {
        expect($request->body())->not->toContain('0.30000000000000004')
            ->and($request['Transactions'][0]['Total'])->toBe(0.3)
            ->and($request['Transactions'][0]['Items'][0]['Quantity'])->toBe(0.3)
            ->and($request['Transactions'][0]['Items'][0]['ItemTotal'])->toBe(0.3);

        return true;
    });
});

it('rejects a start response without a payment id', function (): void {
    Http::fake(['*' => Http::response(['Status' => 'Prepared', 'Errors' => []])]);

    expect(fn () => Barion::startPayment(paymentRequest()))
        ->toThrow(BarionResponseException::class, 'cannot be mapped to [PaymentResponseData]');
});
