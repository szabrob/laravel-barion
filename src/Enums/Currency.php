<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums;

enum Currency: string
{
    case HUF = 'HUF';
    case EUR = 'EUR';
    case USD = 'USD';
    case CZK = 'CZK';
    case RON = 'RON';
    case PLN = 'PLN';
}
