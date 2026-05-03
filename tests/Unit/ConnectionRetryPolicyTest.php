<?php

declare(strict_types=1);

namespace PowerTranz\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PowerTranz\Client\ConnectionRetryPolicy;
use PowerTranz\Exceptions\ApiConnectionException;

final class ConnectionRetryPolicyTest extends TestCase
{
    public function testRetriesUntilSuccess(): void
    {
        $attempts = 0;
        $result = ConnectionRetryPolicy::run(
            maxRetries: 3,
            baseDelayMs: 1,
            multiplier: 2.0,
            maxDelayMs: 100,
            operation: function () use (&$attempts): string {
                $attempts++;
                if ($attempts < 3) {
                    throw new ApiConnectionException('simulated', 0);
                }

                return 'ok';
            },
        );

        self::assertSame('ok', $result);
        self::assertSame(3, $attempts);
    }

    public function testThrowsAfterMaxRetries(): void
    {
        $this->expectException(ApiConnectionException::class);
        ConnectionRetryPolicy::run(2, 1, 2.0, 100, static function (): never {
            throw new ApiConnectionException('always', 0);
        });
    }
}
