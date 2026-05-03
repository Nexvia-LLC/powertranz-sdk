<?php

declare(strict_types=1);

namespace PowerTranz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PowerTranz\Exceptions\ConfigurationException;
use PowerTranz\PowerTranzConfig;

final class PowerTranzConfigTest extends TestCase
{
    public function testFromArrayBuildsConfig(): void
    {
        $c = PowerTranzConfig::fromArray([
            'power_id' => 'id1',
            'power_password' => 'secret',
            'sandbox' => true,
            'timeout' => 45,
            'connect_timeout' => 7,
            'verify_ssl' => false,
            'gateway_key' => 'gk',
            'base_url' => 'https://custom.example/api-root',
            'retry_max_attempts' => 4,
            'retry_base_delay_ms' => 100,
            'retry_backoff_multiplier' => 1.5,
            'retry_max_delay_ms' => 5000,
        ]);

        self::assertSame('id1', $c->getPowerId());
        self::assertSame('secret', $c->getPowerPassword());
        self::assertTrue($c->isSandbox());
        self::assertSame(45, $c->getTimeout());
        self::assertSame(7, $c->getConnectTimeout());
        self::assertFalse($c->shouldVerifySsl());
        self::assertSame('gk', $c->getGatewayKey());
        self::assertSame('https://custom.example/api-root', $c->getBaseUrl());
        self::assertSame(4, $c->getMaxConnectionRetries());
        self::assertSame(100, $c->getRetryBaseDelayMs());
        self::assertSame(1.5, $c->getRetryBackoffMultiplier());
        self::assertSame(5000, $c->getRetryMaxDelayMs());
    }

    public function testFromArrayRejectsEmptyCredentials(): void
    {
        $this->expectException(ConfigurationException::class);
        PowerTranzConfig::fromArray([
            'power_id' => '',
            'power_password' => 'x',
        ]);
    }
}
