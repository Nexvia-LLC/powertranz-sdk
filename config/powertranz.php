<?php

declare(strict_types=1);

use PowerTranz\PowerTranzConfig;

return [

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */

    'power_id' => env('POWERTRANZ_POWER_ID', ''),
    'power_password' => env('POWERTRANZ_POWER_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | When true, requests use the staging host (see PowerTranzConfig::SANDBOX_URL).
    |
    */

    'sandbox' => env('POWERTRANZ_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Optional gateway key
    |--------------------------------------------------------------------------
    |
    | Sent as PowerTranz-GatewayKey when set (optional, per PowerTranz integration notes).
    |
    */

    'gateway_key' => env('POWERTRANZ_GATEWAY_KEY'),

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('POWERTRANZ_TIMEOUT', 30),
    'connect_timeout' => (int) env('POWERTRANZ_CONNECT_TIMEOUT', 10),
    'verify_ssl' => filter_var(env('POWERTRANZ_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),

    /*
    |--------------------------------------------------------------------------
    | Base URL override
    |--------------------------------------------------------------------------
    |
    | Leave null to use PowerTranzConfig defaults for sandbox vs production.
    |
    */

    'base_url' => env('POWERTRANZ_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | SPI token TTL (seconds) — informational; gateway enforces ~5 minutes.
    |--------------------------------------------------------------------------
    */

    'spi_token_ttl' => PowerTranzConfig::SPI_TOKEN_TTL,

    /*
    |--------------------------------------------------------------------------
    | Connection retries (transport failures only)
    |--------------------------------------------------------------------------
    |
    | Retries apply only to ApiConnectionException (timeouts, DNS, reset peer).
    | HTTP 4xx/5xx with a response body are NOT retried. Total attempts = 1 + retry_max_attempts.
    |
    */

    'retry_max_attempts' => (int) env('POWERTRANZ_RETRY_MAX_ATTEMPTS', 0),
    'retry_base_delay_ms' => (int) env('POWERTRANZ_RETRY_BASE_DELAY_MS', 250),
    'retry_backoff_multiplier' => (float) env('POWERTRANZ_RETRY_BACKOFF_MULTIPLIER', 2.0),
    'retry_max_delay_ms' => (int) env('POWERTRANZ_RETRY_MAX_DELAY_MS', 10_000),

];
