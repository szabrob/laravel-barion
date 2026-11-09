<?php

declare(strict_types=1);

use Szabrob\LaravelBarion\Data\Responses\CompletePaymentResponseData;
use Szabrob\LaravelBarion\Data\Responses\UserNameData;
use Szabrob\LaravelBarion\Exceptions\BarionApiException;
use Szabrob\LaravelBarion\Exceptions\BarionResponseException;

it('picks the best display name of a Barion user', function (array $name, ?string $expected): void {
    expect(UserNameData::from($name)->displayName())->toBe($expected);
})->with([
    'organization' => [['LoginName' => 'shop@example.com', 'FirstName' => 'Teszt', 'LastName' => 'Elek', 'OrganizationName' => 'Shop Kft.'], 'Shop Kft.'],
    'full name' => [['LoginName' => 'payer@example.com', 'FirstName' => 'Teszt', 'LastName' => 'Elek', 'OrganizationName' => null], 'Teszt Elek'],
    'login name only' => [['LoginName' => 'payer@example.com', 'FirstName' => null, 'LastName' => null], 'payer@example.com'],
    'nothing' => [[], null],
]);

it('builds an API exception from a Barion error response', function (): void {
    $exception = BarionApiException::fromResponse('/v4/Payment/x/PaymentState', 400, [
        'Errors' => [['ErrorCode' => 'NotExistingPaymentId', 'Title' => 'The given payment id is invalid', 'Description' => 'Invalid id.']],
    ]);

    expect($exception->status)->toBe(400)
        ->and($exception->getMessage())->toBe('Barion API request to [/v4/Payment/x/PaymentState] failed with status 400. [NotExistingPaymentId] The given payment id is invalid Invalid id.')
        ->and($exception->hasErrorCode('NotExistingPaymentId'))->toBeTrue()
        ->and($exception->hasErrorCode('AuthenticationFailed'))->toBeFalse();
});

it('redacts the POS key that Barion echoes in error bodies', function (): void {
    $exception = BarionApiException::fromResponse('/v2/Payment/Complete', 200, [
        'Errors' => [
            ['ErrorCode' => 'PaymentStatusNotValid', 'Title' => 'Invalid payment status', 'AuthData' => 'secret-pos-key'],
            ['ErrorCode' => 'Other', 'AuthData' => null],
        ],
        'POSKey' => 'secret-pos-key',
    ]);

    expect(json_encode($exception->body))->not->toContain('secret-pos-key')
        ->and($exception->body['Errors'][0]['AuthData'])->toBe('[redacted]')
        ->and($exception->body['Errors'][0]['ErrorCode'])->toBe('PaymentStatusNotValid')
        ->and($exception->body['Errors'][1]['AuthData'])->toBeNull()
        ->and($exception->body['POSKey'])->toBe('[redacted]')
        ->and($exception->hasErrorCode('PaymentStatusNotValid'))->toBeTrue();
});

it('redacts the POS key in unmappable response bodies', function (): void {
    $exception = BarionResponseException::unmappable('/v2/Payment/Complete', CompletePaymentResponseData::class, ['AuthData' => 'secret-pos-key'], new RuntimeException('x'));

    expect($exception->body)->toBe(['AuthData' => '[redacted]']);
});

it('builds an API exception from a body without an Errors list', function (): void {
    $exception = BarionApiException::fromResponse('/v2/Payment/Start', 503, []);

    expect($exception->errors)->toBe([])
        ->and($exception->getMessage())->toBe('Barion API request to [/v2/Payment/Start] failed with status 503.');
});
