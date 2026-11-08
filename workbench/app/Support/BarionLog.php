<?php

declare(strict_types=1);

namespace Workbench\App\Support;

use Illuminate\Support\Facades\File;

/**
 * Tiny JSON file store for the workbench: started payments and received callback events.
 */
final class BarionLog
{
    private const int LIMIT = 50;

    /**
     * @param  array<string, mixed>  $payment
     */
    public static function addPayment(array $payment): void
    {
        self::prepend('payments', $payment);
    }

    /**
     * @param  array<string, mixed>  $event
     */
    public static function addEvent(array $event): void
    {
        self::prepend('events', $event);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function payments(): array
    {
        return self::read('payments');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function events(?string $paymentId = null): array
    {
        $events = self::read('events');

        return $paymentId === null
            ? $events
            : array_values(array_filter($events, fn (array $event): bool => ($event['paymentId'] ?? null) === $paymentId));
    }

    public static function clear(): void
    {
        File::delete([self::path('payments'), self::path('events')]);
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private static function prepend(string $name, array $entry): void
    {
        $entries = array_slice([['at' => now()->toDateTimeString(), ...$entry], ...self::read($name)], 0, self::LIMIT);

        File::ensureDirectoryExists(dirname(self::path($name)));
        File::put(self::path($name), json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function read(string $name): array
    {
        $path = self::path($name);

        if (! File::exists($path)) {
            return [];
        }

        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : [];
    }

    private static function path(string $name): string
    {
        return storage_path("app/barion-workbench/{$name}.json");
    }
}
