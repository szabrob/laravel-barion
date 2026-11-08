<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Exceptions;

use LaravelBarion\LaravelBarion\Data\Responses\ApiErrorData;

final class BarionApiException extends BarionException
{
    /**
     * @param  array<int, ApiErrorData>  $errors
     * @param  array<array-key, mixed>  $body
     */
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly array $errors = [],
        public readonly array $body = [],
    ) {
        parent::__construct($message, $status);
    }

    /**
     * @param  array<array-key, mixed>  $body
     */
    public static function fromResponse(string $endpoint, int $status, array $body): self
    {
        $errors = self::errorsFrom($body);

        $details = implode('; ', array_filter(array_map(
            fn (ApiErrorData $error): string => trim(implode(' ', array_filter([
                $error->errorCode !== null ? "[{$error->errorCode}]" : null,
                $error->title,
                $error->description,
            ]))),
            $errors,
        )));

        return new self(
            trim("Barion API request to [{$endpoint}] failed with status {$status}. {$details}"),
            $status,
            $errors,
            $body,
        );
    }

    /**
     * @param  array<array-key, mixed>  $body
     * @return array<int, ApiErrorData>
     */
    private static function errorsFrom(array $body): array
    {
        $errors = $body['Errors'] ?? $body['errors'] ?? null;

        if (is_array($errors) && array_is_list($errors) && $errors !== []) {
            return array_map(
                fn (mixed $error): ApiErrorData => ApiErrorData::from(is_array($error) ? $error : ['Description' => is_scalar($error) ? (string) $error : null]),
                $errors,
            );
        }

        $message = $body['Message'] ?? $body['message'] ?? $body['title'] ?? $body['detail'] ?? null;

        return is_string($message) ? [new ApiErrorData(description: $message)] : [];
    }
}
