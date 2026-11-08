<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums;

enum PaymentStatus: string
{
    case Prepared = 'Prepared';
    case Started = 'Started';
    case InProgress = 'InProgress';
    case Waiting = 'Waiting';
    case Reserved = 'Reserved';
    case Authorized = 'Authorized';
    case Canceled = 'Canceled';
    case Succeeded = 'Succeeded';
    case Failed = 'Failed';
    case PartiallySucceeded = 'PartiallySucceeded';
    case Expired = 'Expired';

    public function isSuccessful(): bool
    {
        return $this === self::Succeeded;
    }

    public function isFailed(): bool
    {
        return in_array($this, [self::Canceled, self::Failed, self::Expired], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Succeeded, self::PartiallySucceeded, self::Canceled, self::Failed, self::Expired], true);
    }
}
