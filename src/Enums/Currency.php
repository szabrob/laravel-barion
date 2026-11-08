<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums;

enum Currency: string
{
    case HUF = 'HUF';
    case EUR = 'EUR';
    case USD = 'USD';
    case CZK = 'CZK';
    case RON = 'RON';
    case PLN = 'PLN';
}
