<?php

declare(strict_types=1);

use LaravelBarion\LaravelBarion\Data\ItemData;
use LaravelBarion\LaravelBarion\Data\Requests\PaymentRequestData;
use LaravelBarion\LaravelBarion\Data\Requests\PaymentTransactionData;
use LaravelBarion\LaravelBarion\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

const PAYMENT_ID = '64157032d3dc4296aedafd4b0994c64e';

function paymentRequest(string $requestId = 'order-1001', ?string $payee = null): PaymentRequestData
{
    return new PaymentRequestData(
        paymentRequestId: $requestId,
        transactions: [
            new PaymentTransactionData(
                posTransactionId: "{$requestId}-t1",
                total: 1500,
                items: [new ItemData(name: 'T-shirt', description: 'Black, M', quantity: 1, unit: 'pcs', unitPrice: 1500, itemTotal: 1500, sku: 'TS-BLK-M')],
                payee: $payee,
            ),
        ],
        redirectUrl: 'https://shop.test/orders/1001',
    );
}

/**
 * @return array<string, mixed>
 */
function paymentStateResponse(string $status = 'Succeeded'): array
{
    return [
        'PaymentId' => PAYMENT_ID,
        'PaymentRequestId' => 'order-1001',
        'OrderNumber' => null,
        'POSId' => 'pos-id',
        'POSName' => 'Test shop',
        'Status' => $status,
        'PaymentType' => 'Immediate',
        'FundingSource' => 'BankCard',
        'FundingInformation' => [
            'BankCard' => ['MaskedPan' => '1234', 'BankCardType' => 'Visa', 'ValidThruYear' => '2030', 'ValidThruMonth' => '12'],
            'AuthorizationCode' => '123456',
            'ProcessResult' => 'Successful',
        ],
        'AllowedFundingSources' => ['All'],
        'GuestCheckout' => true,
        'CreatedAt' => '2026-11-08T10:00:00.123Z',
        'CompletedAt' => '2026-11-08T10:01:30.5',
        'ValidUntil' => '2026-11-08T10:30:00Z',
        'Total' => 1500.0,
        'Currency' => 'HUF',
        'Transactions' => [[
            'TransactionId' => 'trx-1',
            'POSTransactionId' => 'order-1001-t1',
            'TransactionTime' => '2026-11-08T10:01:30Z',
            'Total' => 1500.0,
            'Currency' => 'HUF',
            'Payer' => [
                'Name' => ['LoginName' => 'payer@example.com', 'FirstName' => 'Teszt', 'LastName' => 'Elek', 'OrganizationName' => null],
                'Email' => 'payer@example.com',
            ],
            'Payee' => [
                'Name' => ['LoginName' => 'test-payee@example.com', 'FirstName' => null, 'LastName' => null, 'OrganizationName' => 'Test Shop Kft.'],
                'Email' => 'test-payee@example.com',
            ],
            'Status' => 'Succeeded',
            'TransactionType' => 'CardPayment',
            'Items' => [['Name' => 'T-shirt', 'Description' => 'Black, M', 'Quantity' => 1, 'Unit' => 'pcs', 'UnitPrice' => 1500, 'ItemTotal' => 1500, 'SKU' => 'TS-BLK-M']],
            'POSId' => 'pos-id',
            'PaymentId' => PAYMENT_ID,
        ], [
            'TransactionId' => 'trx-2',
            'Status' => 'SomeStatusAddedLater',
            'Currency' => 'XYZ',
            'TransactionType' => 'CardProcessingFee',
            'Items' => null,
        ]],
        'SuggestedLocale' => 'hu-HU',
        'FraudRiskScore' => null,
        'TraceId' => 'trace-1',
        'PaymentMethod' => 'BankCard',
        'SomeFieldAddedLater' => 'ignored',
    ];
}
