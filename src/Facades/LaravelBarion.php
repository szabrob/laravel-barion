<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelBarion\LaravelBarion\Enums\Environment environment()
 * @method static \LaravelBarion\LaravelBarion\Data\Responses\PaymentResponseData startPayment(\LaravelBarion\LaravelBarion\Data\Requests\PaymentRequestData $request)
 * @method static \LaravelBarion\LaravelBarion\Data\Responses\PaymentStateData paymentState(string $paymentId)
 * @method static \LaravelBarion\LaravelBarion\Data\Responses\RefundResponseData refund(\LaravelBarion\LaravelBarion\Data\Requests\RefundRequestData $request)
 * @method static \LaravelBarion\LaravelBarion\Data\Responses\PaymentOperationResponseData finishReservation(\LaravelBarion\LaravelBarion\Data\Requests\FinishReservationRequestData $request)
 * @method static \LaravelBarion\LaravelBarion\Data\Responses\PaymentOperationResponseData capture(\LaravelBarion\LaravelBarion\Data\Requests\CaptureRequestData $request)
 * @method static \LaravelBarion\LaravelBarion\Data\Responses\PaymentOperationResponseData cancelAuthorization(\LaravelBarion\LaravelBarion\Data\Requests\CancelAuthorizationRequestData $request)
 * @method static \LaravelBarion\LaravelBarion\Data\Responses\PaymentStateData handleCallback(string $paymentId)
 * @method static string gatewayUrl(string $paymentId)
 * @method static string idempotencyKeyFor(string $endpoint, array<array-key, mixed> $body)
 *
 * @see \LaravelBarion\LaravelBarion\LaravelBarion
 */
class LaravelBarion extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelBarion\LaravelBarion\LaravelBarion::class;
    }
}
