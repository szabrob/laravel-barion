<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums\ThreeDSecure;

enum PurchaseType: string
{
    case GoodsAndServicePurchase = 'GoodsAndServicePurchase';
    case CheckAcceptance = 'CheckAcceptance';
    case AccountFunding = 'AccountFunding';
    case QuasiCashTransaction = 'QuasiCashTransaction';
    case PrePaidVacationAndLoan = 'PrePaidVacationAndLoan';
}
