<?php

declare(strict_types=1);

namespace PowerTranz\Support;

use Ramsey\Uuid\Uuid;

/**
 * Generates unique idempotency keys for safe POST retries (HTTP Idempotency-Key header).
 */
final class IdempotencyKey
{
    public static function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
