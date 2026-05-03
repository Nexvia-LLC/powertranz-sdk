<?php

declare(strict_types=1);

namespace PowerTranz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PowerTranz\Request\Beans\BinRange;
use PowerTranz\Request\Beans\Model3DS1AuthenticateBody;
use PowerTranz\Response\OrderResponse;

final class BeansParityTest extends TestCase
{
    public function testBinRangeRoundTrip(): void
    {
        $data = [
            'RangeStart' => '400000',
            'RangeEnd' => '499999',
            'CardScheme' => 'VISA',
            'IssuerCountry' => 'JM',
        ];
        $b = BinRange::fromArray($data);

        self::assertSame('400000', $b->rangeStart);
        self::assertSame('499999', $b->rangeEnd);
        self::assertSame('VISA', $b->cardScheme);
        self::assertSame($data, $b->raw);
        self::assertSame(['RangeStart' => '400000', 'RangeEnd' => '499999', 'CardScheme' => 'VISA', 'IssuerCountry' => 'JM'], $b->toArray());
    }

    public function testModel3DS1FormFields(): void
    {
        $m = new Model3DS1AuthenticateBody(
            paReq: 'pareq-data',
            md: 'md-val',
            termUrl: 'https://merchant.example/cb',
            acsUrl: 'https://acs.example/acs',
        );

        self::assertSame([
            'PaReq' => 'pareq-data',
            'MD' => 'md-val',
            'TermUrl' => 'https://merchant.example/cb',
        ], $m->toFormFields());
    }

    public function testOrderResponseWrapsTransaction(): void
    {
        $order = OrderResponse::fromArray([
            'TransactionIdentifier' => 't1',
            'Approved' => true,
            'TotalAmount' => 5,
            'CurrencyCode' => '840',
            'IsoResponseCode' => '00',
            'ResponseMessage' => 'OK',
        ]);

        self::assertSame('t1', $order->transaction->transactionIdentifier);
        self::assertTrue($order->transaction->approved);
    }
}
