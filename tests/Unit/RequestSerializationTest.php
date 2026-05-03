<?php

declare(strict_types=1);

namespace PowerTranz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PowerTranz\Request\Beans\Address;
use PowerTranz\Request\Beans\ExtendedRequestData;
use PowerTranz\Request\Beans\HostedPageRequestData;
use PowerTranz\Request\Beans\Source;
use PowerTranz\Request\CaptureRequest;
use PowerTranz\Request\RefundRequest;
use PowerTranz\Request\SaleRequest;
use PowerTranz\Request\TransactionSearchRequest;
use PowerTranz\Request\VoidRequest;

final class RequestSerializationTest extends TestCase
{
    public function testSaleRequestSerializesCoreFields(): void
    {
        $sale = new SaleRequest(12.34, '840');
        $sale->setTransactionIdentifier('txn-fixed-id')
            ->setThreeDSecure(true)
            ->setFraudCheck(true)
            ->setTipAmount(1.0)
            ->setTaxAmount(0.5);

        $arr = $sale->toArray();

        self::assertSame('txn-fixed-id', $arr['TransactionIdentifier']);
        self::assertSame(12.34, $arr['TotalAmount']);
        self::assertSame('840', $arr['CurrencyCode']);
        self::assertTrue($arr['ThreeDSecure']);
        self::assertTrue($arr['FraudCheck']);
        self::assertSame(1.0, $arr['TipAmount']);
        self::assertSame(0.5, $arr['TaxAmount']);
    }

    public function testSaleRequestIncludesSourceAndAddresses(): void
    {
        $source = new Source('4111111111111111', '123', '2512', 'Jane Doe');

        $billing = new Address();
        $billing->setLine1('1 Main St');
        $billing->setCity('Kingston');
        $billing->setCountryCode('JM');

        $sale = new SaleRequest(10.0, '388');
        $sale->setSource($source)->setBillingAddress($billing);

        $arr = $sale->toArray();

        self::assertArrayHasKey('Source', $arr);
        self::assertSame('4111111111111111', $arr['Source']['CardPan']);
        self::assertArrayHasKey('BillingAddress', $arr);
        self::assertSame('1 Main St', $arr['BillingAddress']['Line1']);
    }

    public function testExtendedDataHostedPageAndMerchantUrl(): void
    {
        $ext = new ExtendedRequestData();
        $ext->setMerchantResponseUrl('https://example.com/callback');
        $ext->setHostedPage(new HostedPageRequestData('PTZ/TestSet', 'TestPage'));

        $sale = new SaleRequest(5.0, '840');
        $sale->setExtendedData($ext);

        $arr = $sale->toArray()['ExtendedData'];

        self::assertSame('https://example.com/callback', $arr['MerchantResponseUrl']);
        self::assertSame('PTZ/TestSet', $arr['HostedPage']['PageSet']);
        self::assertSame('TestPage', $arr['HostedPage']['PageName']);
    }

    public function testCaptureRequestOptionalFields(): void
    {
        $cap = new CaptureRequest('orig-txn-id', 99.99, '840');
        $cap->setTipAmount(2.0)->setTerminalCode('T01');

        $arr = $cap->toArray();

        self::assertSame('orig-txn-id', $arr['TransactionIdentifier']);
        self::assertSame(99.99, $arr['TotalAmount']);
        self::assertSame(2.0, $arr['TipAmount']);
        self::assertSame('T01', $arr['TerminalCode']);
    }

    public function testRefundRequestIncludesCustomData(): void
    {
        $ref = new RefundRequest('rid', 10.0, '840');
        $ref->setOrderIdentifier('ORD-123')->setCustomData(['note' => 'partial']);

        $arr = $ref->toArray();

        self::assertSame('ORD-123', $arr['OrderIdentifier']);
        self::assertSame(['note' => 'partial'], $arr['CustomData']);
    }

    public function testVoidRequestMinimalAndCustomData(): void
    {
        $v = new VoidRequest('void-txn');
        $v->setCustomData(null);
        self::assertSame(['TransactionIdentifier' => 'void-txn'], $v->toArray());

        $v2 = new VoidRequest('void-2');
        $v2->setTerminalId('T9');
        $arr = $v2->toArray();
        self::assertSame('T9', $arr['TerminalId']);
    }

    public function testTransactionSearchQueryParams(): void
    {
        $q = (new TransactionSearchRequest())
            ->setFromDate('2026-01-01')
            ->setToDate('2026-01-31')
            ->setCardSuffix('4242')
            ->setPage(2)
            ->setPageSize(25);

        $params = $q->toQueryParams();

        self::assertSame([
            'fromDate' => '2026-01-01',
            'toDate' => '2026-01-31',
            'cardSuffix' => '4242',
            'page' => 2,
            'pageSize' => 25,
        ], $params);
    }

    public function testSourcePanTokenSerialization(): void
    {
        $src = Source::fromPanToken('tok_abc');
        self::assertSame(['PanToken' => 'tok_abc'], $src->toArray());
    }
}
