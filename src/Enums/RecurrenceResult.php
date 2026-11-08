<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums;

enum RecurrenceResult: string
{
    case None = 'None';
    case Successful = 'Successful';
    case Failed = 'Failed';
    case NotFound = 'NotFound';
    case ThreeDSAuthenticationRequired = 'ThreeDSAuthenticationRequired';
}
