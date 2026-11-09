<?php

declare(strict_types=1);

namespace Workbench\App\Support;

use Illuminate\Support\Carbon;
use Szabrob\LaravelBarion\Data\ItemData;
use Szabrob\LaravelBarion\Data\Requests\PaymentRequestData;
use Szabrob\LaravelBarion\Data\Requests\PaymentTransactionData;
use Szabrob\LaravelBarion\Data\Responses\PaymentResponseData;
use Szabrob\LaravelBarion\Enums\Currency;
use Szabrob\LaravelBarion\Enums\RecurrenceResult;
use Szabrob\LaravelBarion\Enums\RecurrenceType;
use Szabrob\LaravelBarion\Exceptions\BarionException;
use Szabrob\LaravelBarion\LaravelBarion;

/**
 * Charges the saved card of a workbench schedule: a startPayment() with
 * initiateRecurrence false, the stored recurrenceId, recurrenceType and traceId.
 */
final class TokenCharger
{
    public function __construct(private readonly LaravelBarion $barion) {}

    /**
     * Charge every schedule whose next charge is due.
     *
     * @return array<int, array<string, mixed>> the updated schedules
     */
    public function chargeDue(): array
    {
        $charged = [];

        foreach (BarionLog::schedules() as $schedule) {
            if (Carbon::parse((string) $schedule['nextChargeAt'])->isFuture()) {
                continue;
            }

            try {
                $this->charge($schedule);
            } catch (BarionException) {
                // charge() already stored the error on the schedule.
            }

            $charged[] = BarionLog::schedule((string) $schedule['id']) ?? $schedule;
        }

        return $charged;
    }

    /**
     * @param  array<string, mixed>  $schedule
     */
    public function charge(array $schedule): PaymentResponseData
    {
        $count = (int) $schedule['chargeCount'] + 1;
        // One id per charge: a retry of the same charge returns the same payment instead of charging twice.
        $requestId = "{$schedule['id']}-{$count}";
        $amount = (float) $schedule['amount'];
        $currency = Currency::from((string) $schedule['currency']);

        $schedule['chargeCount'] = $count;
        $schedule['lastChargeAt'] = now()->toDateTimeString();
        $schedule['nextChargeAt'] = now()->addMinutes((int) $schedule['intervalMinutes'])->toDateTimeString();

        try {
            $response = $this->barion->startPayment(new PaymentRequestData(
                paymentRequestId: $requestId,
                transactions: [
                    new PaymentTransactionData(
                        posTransactionId: "{$requestId}-T1",
                        total: $amount,
                        items: [new ItemData(name: 'Workbench előfizetés', description: "{$count}. terhelés", quantity: 1, unit: 'db', unitPrice: $amount, itemTotal: $amount, sku: 'WB-SUB')],
                        comment: 'Workbench ütemezett token terhelés',
                    ),
                ],
                currency: $currency,
                redirectUrl: route('workbench.barion.return'),
                orderNumber: $requestId,
                initiateRecurrence: false,
                recurrenceId: (string) $schedule['recurrenceId'],
                recurrenceType: RecurrenceType::from((string) $schedule['recurrenceType']),
                traceId: is_string($schedule['traceId'] ?? null) ? $schedule['traceId'] : null,
            ));
        } catch (BarionException $exception) {
            $schedule['lastResult'] = class_basename($exception).': '.$exception->getMessage();
            $schedule['lastPaymentId'] = null;
            BarionLog::saveSchedule($schedule);

            throw $exception;
        }

        BarionLog::addPayment([
            'paymentId' => $response->paymentId,
            'paymentRequestId' => $requestId,
            'type' => "Token ({$schedule['recurrenceType']})",
            'total' => $amount,
            'currency' => $currency->value,
            'environment' => $this->barion->environment()->value,
            'gatewayUrl' => $response->gatewayUrl,
        ]);

        $schedule['lastResult'] = ($response->recurrenceResult ?? RecurrenceResult::None)->value.' / '.$response->status->value;
        $schedule['lastPaymentId'] = $response->paymentId;
        BarionLog::saveSchedule($schedule);

        return $response;
    }
}
