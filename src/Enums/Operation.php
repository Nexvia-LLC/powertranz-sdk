<?php

declare(strict_types=1);

namespace PowerTranz\Enums;

/**
 * Gateway REST paths and helpers (HTTP verb, auth header rules, SPI vs standard).
 */
enum Operation: string
{
    // SPI (Simplified Payment Integration) endpoints
    case ALIVE          = '/api/alive';
    case AUTH_3DS       = '/api/spi/auth';
    case SALE_3DS       = '/api/spi/sale';
    case RISK_MGMT_3DS  = '/api/spi/riskmgmt';
    case PAYMENT_3DS    = '/api/spi/payment';

    // Standard (non-SPI) endpoints
    case AUTH           = '/api/auth';
    case SALE           = '/api/sale';
    case RISK           = '/api/riskmgmt';
    case PAYMENT        = '/api/payment';
    case CAPTURE        = '/api/capture';
    case REFUND         = '/api/refund';
    case VOID           = '/api/void';

    // Lookup endpoints
    case GET_TRANSACTION     = '/api/transactions/{transactionId}';
    case SEARCH_TRANSACTIONS = '/api/transactions/search';

    public function method(): string
    {
        return match ($this) {
            self::ALIVE, self::GET_TRANSACTION, self::SEARCH_TRANSACTIONS => 'GET',
            default => 'POST',
        };
    }

    public function requiresAuthHeaders(): bool
    {
        // /payment endpoint does NOT send auth headers per PowerTranz spec
        return !in_array($this, [self::PAYMENT_3DS, self::PAYMENT], true);
    }

    public function isSpi(): bool
    {
        return in_array($this, [
            self::AUTH_3DS, self::SALE_3DS, self::RISK_MGMT_3DS, self::PAYMENT_3DS,
        ], true);
    }
}
