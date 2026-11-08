<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums;

enum Environment: string
{
    case Test = 'test';
    case Production = 'production';

    public function apiUrl(): string
    {
        return match ($this) {
            self::Test => 'https://api.test.barion.com',
            self::Production => 'https://api.barion.com',
        };
    }

    public function gatewayUrl(): string
    {
        return match ($this) {
            self::Test => 'https://secure.test.barion.com/Pay',
            self::Production => 'https://secure.barion.com/Pay',
        };
    }
}
