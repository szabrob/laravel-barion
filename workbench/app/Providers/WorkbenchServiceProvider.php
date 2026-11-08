<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Szabrob\LaravelBarion\Events\PaymentAuthorized;
use Szabrob\LaravelBarion\Events\PaymentFailed;
use Szabrob\LaravelBarion\Events\PaymentRefunded;
use Szabrob\LaravelBarion\Events\PaymentReserved;
use Szabrob\LaravelBarion\Events\PaymentStateReceived;
use Szabrob\LaravelBarion\Events\PaymentSucceeded;
use Workbench\App\Support\BarionLog;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Generated URLs (RedirectUrl, CallbackUrl) always use APP_URL, so a
        // public tunnel URL (ngrok, Expose, Cloudflare Tunnel) works for callbacks.
        $appUrl = config('app.url');

        if (is_string($appUrl) && $appUrl !== '') {
            URL::forceRootUrl($appUrl);

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

        $events = [PaymentStateReceived::class, PaymentSucceeded::class, PaymentFailed::class, PaymentReserved::class, PaymentAuthorized::class, PaymentRefunded::class];

        foreach ($events as $event) {
            Event::listen($event, function (PaymentStateReceived|PaymentSucceeded|PaymentFailed|PaymentReserved|PaymentAuthorized|PaymentRefunded $event): void {
                BarionLog::addEvent([
                    'event' => class_basename($event),
                    'paymentId' => $event->paymentState->paymentId,
                    'status' => $event->paymentState->status->value,
                    'total' => $event->paymentState->total,
                    'currency' => $event->paymentState->currency?->value,
                    'refunded' => $event->paymentState->refundedAmount(),
                ]);
            });
        }
    }
}
