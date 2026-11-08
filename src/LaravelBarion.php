<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion;

use BackedEnum;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use LaravelBarion\LaravelBarion\Data\Requests\CancelAuthorizationRequestData;
use LaravelBarion\LaravelBarion\Data\Requests\CaptureRequestData;
use LaravelBarion\LaravelBarion\Data\Requests\FinishReservationRequestData;
use LaravelBarion\LaravelBarion\Data\Requests\PaymentRequestData;
use LaravelBarion\LaravelBarion\Data\Requests\RefundRequestData;
use LaravelBarion\LaravelBarion\Data\Responses\PaymentOperationResponseData;
use LaravelBarion\LaravelBarion\Data\Responses\PaymentResponseData;
use LaravelBarion\LaravelBarion\Data\Responses\PaymentStateData;
use LaravelBarion\LaravelBarion\Data\Responses\RefundResponseData;
use LaravelBarion\LaravelBarion\Enums\Environment;
use LaravelBarion\LaravelBarion\Events\PaymentFailed;
use LaravelBarion\LaravelBarion\Events\PaymentStateReceived;
use LaravelBarion\LaravelBarion\Events\PaymentSucceeded;
use LaravelBarion\LaravelBarion\Exceptions\BarionApiException;
use LaravelBarion\LaravelBarion\Exceptions\BarionConnectionException;
use LaravelBarion\LaravelBarion\Exceptions\BarionException;
use Throwable;

class LaravelBarion
{
    public const string CALLBACK_ROUTE = 'laravel-barion.callback';

    public function __construct(
        private readonly Config $config,
        private readonly HttpFactory $http,
        private readonly Dispatcher $events,
        private readonly UrlGenerator $url,
    ) {}

    public function environment(): Environment
    {
        $environment = $this->config->get('laravel-barion.environment');

        if ($environment instanceof Environment) {
            return $environment;
        }

        return (is_string($environment) ? Environment::tryFrom($environment) : null)
            ?? throw BarionException::invalidEnvironment($environment);
    }

    /**
     * Start a payment (v2 Payment/Start) and return the gateway URL to redirect the customer to.
     */
    public function startPayment(PaymentRequestData $request): PaymentResponseData
    {
        $body = $request->toArray();

        $body['RedirectUrl'] ??= $this->config->get('laravel-barion.redirect_url');
        $body['CallbackUrl'] ??= $this->defaultCallbackUrl();
        $body['Transactions'] = array_map($this->withDefaultPayee(...), $this->listOfArrays($body['Transactions'] ?? []));

        $response = $this->post('/v2/Payment/Start', $body, $request->idempotencyKey);

        $paymentId = $response['PaymentId'] ?? null;
        $response['GatewayUrl'] = is_string($paymentId) ? $this->gatewayUrl($paymentId) : null;

        return PaymentResponseData::from($response);
    }

    /**
     * Query the verified state of a payment from the v4 PaymentState API.
     */
    public function paymentState(string $paymentId): PaymentStateData
    {
        $endpoint = '/v4/Payment/'.rawurlencode($paymentId).'/PaymentState';

        return PaymentStateData::from($this->send('GET', $endpoint));
    }

    public function refund(RefundRequestData $request): RefundResponseData
    {
        return RefundResponseData::from(
            $this->post('/v2/Payment/Refund', $request->toArray(), $request->idempotencyKey),
        );
    }

    public function finishReservation(FinishReservationRequestData $request): PaymentOperationResponseData
    {
        return PaymentOperationResponseData::from(
            $this->post('/v2/Payment/FinishReservation', $request->toArray(), $request->idempotencyKey),
        );
    }

    public function capture(CaptureRequestData $request): PaymentOperationResponseData
    {
        return PaymentOperationResponseData::from(
            $this->post('/v2/Payment/Capture', $request->toArray(), $request->idempotencyKey),
        );
    }

    public function cancelAuthorization(CancelAuthorizationRequestData $request): PaymentOperationResponseData
    {
        return PaymentOperationResponseData::from(
            $this->post('/v2/Payment/CancelAuthorization', $request->toArray(), $request->idempotencyKey),
        );
    }

    /**
     * Handle a Barion callback. The callback is unsigned, so the paymentId is
     * only used to query the PaymentState API; the events carry that result.
     */
    public function handleCallback(string $paymentId): PaymentStateData
    {
        $state = $this->paymentState($paymentId);

        $this->events->dispatch(new PaymentStateReceived($state));

        if ($state->status->isSuccessful()) {
            $this->events->dispatch(new PaymentSucceeded($state));
        } elseif ($state->status->isFailed()) {
            $this->events->dispatch(new PaymentFailed($state));
        }

        return $state;
    }

    public function gatewayUrl(string $paymentId): string
    {
        return $this->environment()->gatewayUrl().'?'.http_build_query(['id' => $paymentId]);
    }

    /**
     * The Idempotency-Key used when a request does not define its own: the same
     * operation with the same body always gets the same key, so retrying it
     * (even from a re-queued job) cannot execute it twice.
     *
     * @param  array<array-key, mixed>  $body
     */
    public function idempotencyKeyFor(string $endpoint, array $body): string
    {
        $hash = hash('sha256', $this->environment()->value.'|'.$endpoint.'|'.json_encode($body, JSON_THROW_ON_ERROR));

        return sprintf(
            '%s-%s-8%s-%s%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            dechex(8 | (hexdec($hash[16]) & 3)),
            substr($hash, 17, 3),
            substr($hash, 20, 12),
        );
    }

    /**
     * @param  array<array-key, mixed>  $body
     * @return array<array-key, mixed>
     */
    private function post(string $endpoint, array $body, ?string $idempotencyKey): array
    {
        $body = $this->withoutNulls($body);

        return $this->send('POST', $endpoint, $body, $idempotencyKey ?? $this->idempotencyKeyFor($endpoint, $body));
    }

    /**
     * @param  array<array-key, mixed>  $body
     * @return array<array-key, mixed>
     */
    private function send(string $method, string $endpoint, array $body = [], ?string $idempotencyKey = null): array
    {
        $request = $this->pendingRequest();

        if ($idempotencyKey !== null) {
            $request->withHeaders(['Idempotency-Key' => $idempotencyKey]);
        }

        try {
            /** @var Response $response */
            $response = $method === 'GET' ? $request->get($endpoint) : $request->post($endpoint, $body);
        } catch (ConnectionException $exception) {
            throw new BarionConnectionException("Could not connect to the Barion API [{$endpoint}]: {$exception->getMessage()}", 0, $exception);
        }

        $json = $response->json();
        $json = is_array($json) ? $json : [];

        $errors = $json['Errors'] ?? null;

        if ($response->failed() || (is_array($errors) && $errors !== [])) {
            throw BarionApiException::fromResponse($endpoint, $response->status(), $json);
        }

        return $this->withoutNulls($json);
    }

    private function pendingRequest(): PendingRequest
    {
        $environment = $this->environment();
        $posKey = $this->config->get("laravel-barion.{$environment->value}.pos_key");

        if (! is_string($posKey) || $posKey === '') {
            throw BarionException::missingPosKey($environment->value);
        }

        return $this->http
            ->baseUrl($environment->apiUrl())
            ->acceptJson()
            ->asJson()
            ->withHeaders(['x-pos-key' => $posKey])
            ->timeout($this->intConfig('http.timeout', 30))
            ->connectTimeout($this->intConfig('http.connect_timeout', 10))
            ->retry(
                $this->intConfig('http.retry.times', 2) + 1,
                $this->intConfig('http.retry.sleep', 200),
                fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->serverError()),
                throw: false,
            );
    }

    private function defaultCallbackUrl(): ?string
    {
        if (! $this->config->get('laravel-barion.callback.enabled')) {
            return null;
        }

        return $this->url->route(self::CALLBACK_ROUTE);
    }

    /**
     * @param  array<array-key, mixed>  $transaction
     * @return array<array-key, mixed>
     */
    private function withDefaultPayee(array $transaction): array
    {
        if (is_string($transaction['Payee'] ?? null) && $transaction['Payee'] !== '') {
            return $transaction;
        }

        $environment = $this->environment()->value;
        $payee = $this->config->get("laravel-barion.{$environment}.payee");

        if (! is_string($payee) || $payee === '') {
            throw BarionException::missingPayee($environment);
        }

        $transaction['Payee'] = $payee;

        return $transaction;
    }

    /**
     * @return array<int, array<array-key, mixed>>
     */
    private function listOfArrays(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, is_array(...))) : [];
    }

    /**
     * Drops null values and turns enums nested in plain arrays into their values.
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    private function withoutNulls(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $result[$key] = match (true) {
                is_array($value) => $this->withoutNulls($value),
                $value instanceof BackedEnum => $value->value,
                default => $value,
            };
        }

        return $result;
    }

    private function intConfig(string $key, int $default): int
    {
        $value = $this->config->get("laravel-barion.{$key}", $default);

        return is_numeric($value) ? (int) $value : $default;
    }
}
