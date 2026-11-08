<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Facades;

use Illuminate\Support\Facades\Facade;
use Szabrob\LaravelBarion\LaravelBarion;

/**
 * @method static \Szabrob\LaravelBarion\Enums\Environment environment()
 * @method static \Szabrob\LaravelBarion\Data\Responses\PaymentResponseData startPayment(\Szabrob\LaravelBarion\Data\Requests\PaymentRequestData $request)
 * @method static \Szabrob\LaravelBarion\Data\Responses\PaymentStateData paymentState(string $paymentId)
 * @method static \Szabrob\LaravelBarion\Data\Responses\RefundResponseData refund(\Szabrob\LaravelBarion\Data\Requests\RefundRequestData $request)
 * @method static \Szabrob\LaravelBarion\Data\Responses\PaymentOperationResponseData finishReservation(\Szabrob\LaravelBarion\Data\Requests\FinishReservationRequestData $request)
 * @method static \Szabrob\LaravelBarion\Data\Responses\PaymentOperationResponseData capture(\Szabrob\LaravelBarion\Data\Requests\CaptureRequestData $request)
 * @method static \Szabrob\LaravelBarion\Data\Responses\PaymentOperationResponseData cancelAuthorization(\Szabrob\LaravelBarion\Data\Requests\CancelAuthorizationRequestData $request)
 * @method static \Szabrob\LaravelBarion\Data\Responses\PaymentStateData handleCallback(string $paymentId)
 * @method static string gatewayUrl(string $paymentId)
 * @method static string idempotencyKeyFor(string $endpoint, array<array-key, mixed> $body)
 *
 * @see LaravelBarion
 */
class Barion extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelBarion::class;
    }
}
