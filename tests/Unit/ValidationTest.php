<?php

declare(strict_types=1);

namespace PowerTranz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PowerTranz\Exceptions\ValidationException;
use PowerTranz\Request\Beans\HostedPageRequestData;
use PowerTranz\Request\Beans\Source;
use PowerTranz\Request\CaptureRequest;
use PowerTranz\Request\RefundRequest;
use PowerTranz\Request\SaleRequest;
use PowerTranz\Request\VoidRequest;

final class ValidationTest extends TestCase
{
    public function testSaleRequestRejectsNonPositiveAmount(): void
    {
        $this->expectException(ValidationException::class);
        new SaleRequest(0.0, '840');
    }

    public function testSaleRequestRejectsInvalidCurrency(): void
    {
        $this->expectException(ValidationException::class);
        new SaleRequest(10.0, 'US');
    }

    public function testHostedPageRequiresPtzPrefix(): void
    {
        $this->expectException(ValidationException::class);
        new HostedPageRequestData('BadSet', 'Page');
    }

    public function testCaptureRequiresTransactionId(): void
    {
        $this->expectException(ValidationException::class);
        new CaptureRequest('   ', 10.0, '840');
    }

    public function testRefundRequiresPositiveAmount(): void
    {
        $this->expectException(ValidationException::class);
        new RefundRequest('tid', -1.0, '840');
    }

    public function testVoidRequiresTransactionId(): void
    {
        $this->expectException(ValidationException::class);
        new VoidRequest('');
    }

    public function testSourceValidatesPanLength(): void
    {
        $this->expectException(ValidationException::class);
        new Source('123', '123', '2512');
    }
}
