<?php

declare(strict_types=1);

use Szabrob\LaravelBarion\Data\ItemData;
use Szabrob\LaravelBarion\Data\Requests\PaymentRequestData;
use Szabrob\LaravelBarion\Data\Requests\PaymentTransactionData;
use Szabrob\LaravelBarion\Tests\TestCase;

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

/**
 * A refunded payment as the v4 PaymentState API returns it: the payment stays
 * Succeeded, its Total drops, and a RefundToBankCard transaction appears.
 *
 * @return array<string, mixed>
 */
function refundedPaymentStateResponse(): array
{
    $response = paymentStateResponse();
    $response['Total'] = 1000.0;
    $response['Transactions'] = [
        $response['Transactions'][0],
        ['TransactionId' => 'trx-fee', 'Total' => 15.0, 'Currency' => 'HUF', 'Status' => 'Deleted', 'TransactionType' => 'BarionBalanceProcessingFee', 'POSTransactionId' => null, 'RelatedId' => null],
        ['TransactionId' => 'trx-refund-1', 'POSTransactionId' => 'refund-1', 'Total' => 500.0, 'Currency' => 'HUF', 'Status' => 'Succeeded', 'TransactionType' => 'RefundToBankCard', 'RelatedId' => 'trx-1', 'Items' => []],
        ['TransactionId' => 'trx-refund-2', 'POSTransactionId' => 'refund-2', 'Total' => 200.0, 'Currency' => 'HUF', 'Status' => 'Rejected', 'TransactionType' => 'RefundToBankCard', 'RelatedId' => 'trx-1'],
        ['TransactionId' => 'trx-storno', 'Total' => 100.0, 'Currency' => 'HUF', 'Status' => 'Succeeded', 'TransactionType' => 'StornoUnSuccessfulRefundToBankCard', 'RelatedId' => 'trx-1'],
    ];

    return $response;
}
