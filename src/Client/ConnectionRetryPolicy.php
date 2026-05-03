<?php

declare(strict_types=1);

namespace PowerTranz\Client;

use PowerTranz\Exceptions\ApiConnectionException;

/**
 * Retries a callable on {@see ApiConnectionException} with exponential backoff.
 */
final class ConnectionRetryPolicy
{
    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public static function run(
        int $maxRetries,
        int $baseDelayMs,
        float $multiplier,
        int $maxDelayMs,
        callable $operation,
    ): mixed {
        $attempt = 0;
        while (true) {
            try {
                return $operation();
            } catch (ApiConnectionException $e) {
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                $delayMs = (int) ($baseDelayMs * ($multiplier ** $attempt));
                if ($delayMs > $maxDelayMs) {
                    $delayMs = $maxDelayMs;
                }
                usleep($delayMs * 1000);
                $attempt++;
            }
        }
    }
}
