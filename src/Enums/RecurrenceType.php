<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums;

enum RecurrenceType: string
{
    case MerchantInitiatedPayment = 'MerchantInitiatedPayment';
    case OneClickPayment = 'OneClickPayment';
    case RecurringPayment = 'RecurringPayment';
}
