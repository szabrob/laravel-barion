<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums\ThreeDSecure;

enum AccountChangeIndicator: string
{
    case ChangedDuringThisTransaction = 'ChangedDuringThisTransaction';
    case LessThan30Days = 'LessThan30Days';
    case Between30And60Days = 'Between30And60Days';
    case MoreThan60Days = 'MoreThan60Days';
}
