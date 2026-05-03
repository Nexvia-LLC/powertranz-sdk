<?php

declare(strict_types=1);

namespace PowerTranz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PowerTranz\Enums\AuthenticationStatus;
use PowerTranz\Enums\FraudCheckResponseCode;
use PowerTranz\Enums\IsoResponseCode;
use PowerTranz\Response\TransactionResponse;

final class TransactionResponseParsingTest extends TestCase
{
    public function testFromArrayMapsCoreFields(): void
    {
        $raw = [
            'TransactionType' => 1,
            'Approved' => true,
            'AuthorizationCode' => 'AUTH1',
            'TransactionIdentifier' => 'tid-1',
            'TotalAmount' => 100.5,
            'CurrencyCode' => '840',
            'IsoResponseCode' => '00',
            'ResponseMessage' => 'OK',
            'PanToken' => 'ptok',
            'CardSuffix' => '4242',
            'CardBrand' => 'Visa',
            'RRN' => 'rrn1',
            'HostRRN' => 'hrrn',
            'SpiToken' => 'spi-xyz',
            'RedirectData' => '<html/>',
            'OrderIdentifier' => 'ORD-1',
            'ExternalIdentifier' => 'EXT',
            'CustomData' => ['k' => 'v'],
            'Host' => 'h1',
            'Errors' => [],
        ];

        $r = TransactionResponse::fromArray($raw);

        self::assertSame(1, $r->transactionType);
        self::assertTrue($r->approved);
        self::assertSame('AUTH1', $r->authorizationCode);
        self::assertSame('tid-1', $r->transactionIdentifier);
        self::assertSame(100.5, $r->totalAmount);
        self::assertSame('840', $r->currencyCode);
        self::assertSame(IsoResponseCode::Approved, $r->isoResponseCode);
        self::assertSame('ptok', $r->panToken);
        self::assertSame('4242', $r->cardSuffix);
        self::assertSame('spi-xyz', $r->spiToken);
        self::assertSame(['k' => 'v'], $r->customData);
        self::assertSame($raw, $r->raw);
    }

    public function testFromArrayMapsIso89FailedAuthentication(): void
    {
        $r = TransactionResponse::fromArray([
            'TransactionIdentifier' => 'tid-89',
            'Approved' => false,
            'TotalAmount' => 1,
            'CurrencyCode' => '388',
            'IsoResponseCode' => '89',
            'ResponseMessage' => 'Failed authentication',
        ]);

        self::assertSame(IsoResponseCode::FailedAuthentication, $r->isoResponseCode);
        self::assertFalse($r->approved);
    }

    public function testRiskManagementNestedBeans(): void
    {
        $raw = [
            'TransactionIdentifier' => 'x',
            'Approved' => false,
            'TotalAmount' => 0,
            'CurrencyCode' => '840',
            'IsoResponseCode' => 'SP4',
            'ResponseMessage' => 'SPI',
            'RiskManagement' => [
                'AvsResponseCode' => 'M',
                'CvvResponseCode' => 'P',
                'ThreeDSecure' => [
                    'Eci' => '05',
                    'AuthenticationStatus' => 'Y',
                    'RedirectData' => 'rd',
                ],
                'FraudCheck' => [
                    'FcProvider' => 'Kount',
                    'FcResponseCode' => 'A',
                    'FcScore' => '42',
                    'FcDetails' => [['Code' => '1']],
                ],
            ],
        ];

        $r = TransactionResponse::fromArray($raw);

        self::assertNotNull($r->riskManagement);
        self::assertSame('M', $r->riskManagement->avsResponseCode);
        self::assertSame('P', $r->riskManagement->cvvResponseCode);

        $tds = $r->riskManagement->threeDSecure;
        self::assertNotNull($tds);
        self::assertSame(AuthenticationStatus::Authenticated, $tds->authenticationStatus);
        self::assertSame('rd', $tds->redirectData);

        $fc = $r->riskManagement->fraudCheck;
        self::assertNotNull($fc);
        self::assertSame(FraudCheckResponseCode::Approved, $fc->fcResponseCode);
        self::assertSame(42, $fc->getScore());
    }

    public function testBillingAddressFromArray(): void
    {
        $raw = [
            'TransactionIdentifier' => 't',
            'Approved' => true,
            'TotalAmount' => 1,
            'CurrencyCode' => '840',
            'IsoResponseCode' => '00',
            'ResponseMessage' => 'OK',
            'BillingAddress' => [
                'Line1' => '10 Road',
                'City' => 'Town',
            ],
        ];

        $r = TransactionResponse::fromArray($raw);

        self::assertNotNull($r->billingAddress);
        self::assertSame('10 Road', $r->billingAddress->getLine1());
        self::assertSame('Town', $r->billingAddress->getCity());
    }

    public function testCanProceedToPaymentRequiresSpiTokenAndRiskGates(): void
    {
        $noSpi = TransactionResponse::fromArray([
            'TransactionIdentifier' => 'a',
            'Approved' => true,
            'TotalAmount' => 1,
            'CurrencyCode' => '840',
            'IsoResponseCode' => 'SP4',
            'ResponseMessage' => '',
            'SpiToken' => '',
        ]);
        self::assertFalse($noSpi->canProceedToPayment());

        $withSpi = TransactionResponse::fromArray([
            'TransactionIdentifier' => 'b',
            'Approved' => true,
            'TotalAmount' => 1,
            'CurrencyCode' => '840',
            'IsoResponseCode' => 'SP4',
            'ResponseMessage' => '',
            'SpiToken' => 'spi',
        ]);
        self::assertTrue($withSpi->canProceedToPayment());

        $tdsFail = TransactionResponse::fromArray([
            'TransactionIdentifier' => 'c',
            'Approved' => true,
            'TotalAmount' => 1,
            'CurrencyCode' => '840',
            'IsoResponseCode' => 'SP4',
            'ResponseMessage' => '',
            'SpiToken' => 'spi',
            'RiskManagement' => [
                'ThreeDSecure' => [
                    'AuthenticationStatus' => 'N',
                ],
            ],
        ]);
        self::assertFalse($tdsFail->canProceedToPayment());
    }
}
