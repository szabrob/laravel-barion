<?php

declare(strict_types=1);

use Szabrob\LaravelBarion\Enums\Environment;
use Szabrob\LaravelBarion\Enums\PaymentStatus;

it('maps environments to the Barion API and gateway hosts', function (Environment $environment, string $api, string $gateway): void {
    expect($environment->apiUrl())->toBe($api)
        ->and($environment->gatewayUrl())->toBe($gateway);
})->with([
    'test' => [Environment::Test, 'https://api.test.barion.com', 'https://secure.test.barion.com/Pay'],
    'production' => [Environment::Production, 'https://api.barion.com', 'https://secure.barion.com/Pay'],
]);

it('classifies payment statuses', function (PaymentStatus $status, bool $successful, bool $failed, bool $final): void {
    expect($status->isSuccessful())->toBe($successful)
        ->and($status->isFailed())->toBe($failed)
        ->and($status->isFinal())->toBe($final);
})->with([
    'prepared' => [PaymentStatus::Prepared, false, false, false],
    'reserved' => [PaymentStatus::Reserved, false, false, false],
    'authorized' => [PaymentStatus::Authorized, false, false, false],
    'succeeded' => [PaymentStatus::Succeeded, true, false, true],
    'partially succeeded' => [PaymentStatus::PartiallySucceeded, false, false, true],
    'canceled' => [PaymentStatus::Canceled, false, true, true],
    'failed' => [PaymentStatus::Failed, false, true, true],
    'expired' => [PaymentStatus::Expired, false, true, true],
]);
