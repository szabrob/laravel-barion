<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Szabrob\LaravelBarion\Exceptions\BarionApiException;
use Szabrob\LaravelBarion\LaravelBarion;

final class CallbackController
{
    /**
     * Barion sends only the paymentId, unsigned. Nothing else from the request
     * is used: the payment state always comes from the PaymentState API. A
     * non-200 answer makes Barion retry the callback later.
     */
    public function __invoke(Request $request, LaravelBarion $barion): Response
    {
        $paymentId = $request->input('paymentId', $request->input('PaymentId'));

        if (! is_string($paymentId) || preg_match('/^[0-9a-fA-F]{8}-?(?:[0-9a-fA-F]{4}-?){3}[0-9a-fA-F]{12}$/', $paymentId) !== 1) {
            return new Response('Invalid paymentId.', 400);
        }

        try {
            $barion->handleCallback($paymentId);
        } catch (BarionApiException $exception) {
            // A well-formed id that Barion does not know is not a real callback:
            // answer quietly instead of logging an error for every probe.
            if ($exception->hasErrorCode('NotExistingPaymentId')) {
                return new Response('Unknown paymentId.', 404);
            }

            throw $exception;
        }

        return new Response('', 200);
    }
}
