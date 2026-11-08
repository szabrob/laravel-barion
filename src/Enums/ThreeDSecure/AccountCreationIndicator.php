<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums\ThreeDSecure;

enum AccountCreationIndicator: string
{
    case NoAccount = 'NoAccount';
    case CreatedDuringThisTransaction = 'CreatedDuringThisTransaction';
    case LessThan30Days = 'LessThan30Days';
    case Between30And60Days = 'Between30And60Days';
    case MoreThan60Days = 'MoreThan60Days';
}
