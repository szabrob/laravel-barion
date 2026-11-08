<?php

declare(strict_types=1);

use LaravelBarion\LaravelBarion\Data\Responses\UserNameData;
use LaravelBarion\LaravelBarion\Exceptions\BarionApiException;

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

it('builds an API exception from a body without an Errors list', function (): void {
    $exception = BarionApiException::fromResponse('/v2/Payment/Start', 503, []);

    expect($exception->errors)->toBe([])
        ->and($exception->getMessage())->toBe('Barion API request to [/v2/Payment/Start] failed with status 503.');
});
