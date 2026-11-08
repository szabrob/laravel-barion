<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums;

enum FundingSource: string
{
    case All = 'All';
    case Balance = 'Balance';
    case BankCard = 'Bankcard';
    case BankTransfer = 'BankTransfer';
    case ApplePay = 'ApplePay';
    case GooglePay = 'GooglePay';
}
