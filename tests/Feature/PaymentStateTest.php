<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LaravelBarion\LaravelBarion\Enums\Currency;
use LaravelBarion\LaravelBarion\Enums\PaymentStatus;
use LaravelBarion\LaravelBarion\Enums\PaymentType;
use LaravelBarion\LaravelBarion\Enums\TransactionStatus;
use LaravelBarion\LaravelBarion\Exceptions\BarionApiException;
use LaravelBarion\LaravelBarion\Exceptions\BarionConnectionException;
use LaravelBarion\LaravelBarion\Facades\LaravelBarion;

it('queries the v4 PaymentState endpoint with the x-pos-key header', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse())]);

    LaravelBarion::paymentState(PAYMENT_ID);

    Http::assertSent(function (Request $request): bool {
        expect($request->method())->toBe('GET')
            ->and($request->url())->toBe('https://api.test.barion.com/v4/Payment/'.PAYMENT_ID.'/PaymentState')
            ->and($request->header('x-pos-key'))->toBe(['test-pos-key'])
            ->and($request->url())->not->toContain('POSKey')
            ->and($request->url())->not->toContain('GetPaymentState');

        return true;
    });
});

it('maps the payment state to typed DTOs', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse())]);

    $state = LaravelBarion::paymentState(PAYMENT_ID);

    expect($state->paymentId)->toBe(PAYMENT_ID)
        ->and($state->status)->toBe(PaymentStatus::Succeeded)
        ->and($state->paymentType)->toBe(PaymentType::Immediate)
        ->and($state->currency)->toBe(Currency::HUF)
        ->and($state->total)->toBe(1500.0)
        ->and($state->posName)->toBe('Test shop')
        ->and($state->orderNumber)->toBeNull()
        ->and($state->fraudRiskScore)->toBeNull()
        ->and($state->fundingInformation?->bankCard?->maskedPan)->toBe('1234')
        ->and($state->createdAt?->format('Y-m-d H:i:s.v e'))->toBe('2026-11-08 10:00:00.123 Z')
        ->and($state->completedAt?->format('Y-m-d H:i:s e'))->toBe('2026-11-08 10:01:30 UTC')
        ->and($state->transactions)->toHaveCount(2);

    $transaction = $state->transactions[0];

    expect($transaction->posTransactionId)->toBe('order-1001-t1')
        ->and($transaction->status)->toBe(TransactionStatus::Succeeded)
        ->and($transaction->payer?->email)->toBe('payer@example.com')
        ->and($transaction->payer?->name?->loginName)->toBe('payer@example.com')
        ->and($transaction->payer?->name?->displayName())->toBe('Teszt Elek')
        ->and($transaction->payee?->name?->displayName())->toBe('Test Shop Kft.')
        ->and($transaction->transactionType)->toBe('CardPayment')
        ->and($transaction->items[0]->sku)->toBe('TS-BLK-M');
});

it('tolerates unknown secondary enum values from Barion', function (): void {
    Http::fake(['*' => Http::response(paymentStateResponse())]);

    $transaction = LaravelBarion::paymentState(PAYMENT_ID)->transactions[1];

    expect($transaction->status)->toBeNull()
        ->and($transaction->currency)->toBeNull()
        ->and($transaction->items)->toBe([]);
});

it('throws an API exception with the Barion errors', function (): void {
    Http::fake(['*' => Http::response([
        'Errors' => [[
            'ErrorCode' => 'AuthenticationFailed',
            'Title' => 'Authentication failed',
            'Description' => 'Invalid POS key.',
            'EndPoint' => 'https://api.test.barion.com/v4/Payment/x/PaymentState',
        ]],
    ], 401)]);

    try {
        LaravelBarion::paymentState(PAYMENT_ID);
        $this->fail('Expected exception.');
    } catch (BarionApiException $exception) {
        expect($exception->status)->toBe(401)
            ->and($exception->errors)->toHaveCount(1)
            ->and($exception->errors[0]->errorCode)->toBe('AuthenticationFailed')
            ->and($exception->getMessage())->toContain('[AuthenticationFailed] Authentication failed Invalid POS key.');
    }
});

it('throws an API exception for errors in a successful response', function (): void {
    Http::fake(['*' => Http::response(['Errors' => [['ErrorCode' => 'X', 'Title' => 'Bad']]])]);

    expect(fn () => LaravelBarion::paymentState(PAYMENT_ID))->toThrow(BarionApiException::class, '[X] Bad');
});

it('reads problem detail style error messages', function (): void {
    Http::fake(['*' => Http::response(['title' => 'Payment not found'], 404)]);

    expect(fn () => LaravelBarion::paymentState(PAYMENT_ID))->toThrow(BarionApiException::class, 'Payment not found');
});

it('does not retry client errors', function (): void {
    Http::fake(['*' => Http::response(['Errors' => [['Title' => 'Bad request']]], 400)]);

    expect(fn () => LaravelBarion::paymentState(PAYMENT_ID))->toThrow(BarionApiException::class);

    Http::assertSentCount(1);
});

it('wraps connection failures', function (): void {
    $attempts = 0;
    Http::fake(['*' => function () use (&$attempts): never {
        $attempts++;

        throw new ConnectionException('timeout');
    }]);

    expect(fn () => LaravelBarion::paymentState(PAYMENT_ID))->toThrow(BarionConnectionException::class, 'timeout')
        ->and($attempts)->toBe(3);
});
