<?php

declare(strict_types=1);

namespace PowerTranz\Exceptions;

use RuntimeException;
use Throwable;

class PowerTranzException extends RuntimeException {}
class ConfigurationException extends PowerTranzException {}
class ValidationException extends PowerTranzException {}

class ApiConnectionException extends PowerTranzException
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('PowerTranz API connection failed: ' . $message, $code, $previous);
    }
}

class ApiResponseException extends PowerTranzException
{
    public function __construct(
        private readonly int $httpStatusCode,
        private readonly string $responseBody,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('PowerTranz API returned HTTP %d. Body: %s', $httpStatusCode, $responseBody),
            $httpStatusCode,
            $previous
        );
    }
    public function getHttpStatusCode(): int    { return $this->httpStatusCode; }
    public function getResponseBody(): string   { return $this->responseBody; }
}

class InvalidResponseException extends PowerTranzException
{
    public function __construct(string $body, ?Throwable $previous = null)
    {
        parent::__construct('Failed to parse PowerTranz API response: ' . $body, 0, $previous);
    }
}

class SpiTokenExpiredException extends PowerTranzException
{
    public function __construct()
    {
        parent::__construct('SpiToken has expired (5-minute TTL). Initiate a new Auth/Sale/RiskMgmt request.');
    }
}
