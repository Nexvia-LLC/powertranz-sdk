<?php

declare(strict_types=1);

namespace PowerTranz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PowerTranz\Support\SensitiveDataRedactor;

final class SensitiveDataRedactorTest extends TestCase
{
    public function testRedactsCardPanAndPasswordKeys(): void
    {
        $r = new SensitiveDataRedactor();
        $out = $r->redactArray([
            'CardPan' => '4111111111111111',
            'PowerTranz-PowerTranzPassword' => 'secret',
            'Nested' => ['CardCvv' => '123'],
        ]);

        self::assertSame('[REDACTED]', $out['CardPan']);
        self::assertSame('[REDACTED]', $out['PowerTranz-PowerTranzPassword']);
        self::assertSame('[REDACTED]', $out['Nested']['CardCvv']);
    }

    public function testRedactsPanDigitsInStrings(): void
    {
        $r = new SensitiveDataRedactor();
        self::assertStringContainsString('[REDACTED]', $r->redactString('token 4111111111111111 end'));
    }

    public function testRedactsSensitiveHeaders(): void
    {
        $r = new SensitiveDataRedactor();
        $h = $r->redactHeaders([
            'PowerTranz-PowerTranzPassword' => 'x',
            'PowerTranz-PowerTranzId' => 'merchant',
            'Accept' => 'application/json',
        ]);

        self::assertSame('[REDACTED]', $h['PowerTranz-PowerTranzPassword']);
        self::assertSame('[REDACTED]', $h['PowerTranz-PowerTranzId']);
        self::assertSame('application/json', $h['Accept']);
    }
}
