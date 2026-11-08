<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelBarion\LaravelBarion\LaravelBarion;

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

        $barion->handleCallback($paymentId);

        return new Response('', 200);
    }
}
