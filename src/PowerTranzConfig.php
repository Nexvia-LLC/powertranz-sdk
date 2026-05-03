<?php

declare(strict_types=1);

namespace PowerTranz;

use PowerTranz\Exceptions\ConfigurationException;
use Psr\Log\LoggerInterface;

final class PowerTranzConfig
{
    public const SANDBOX_URL    = 'https://staging.ptranz.com';
    public const PRODUCTION_URL = 'https://staging.powertranz.com'; // Update when live URL is confirmed
    public const SPI_TOKEN_TTL  = 300; // 5 minutes

    private string $baseUrl;

    public function __construct(
        private readonly string $powerId,
        private readonly string $powerPassword,
        private readonly bool   $sandbox         = false,
        private readonly int    $timeout         = 30,
        private readonly int    $connectTimeout  = 10,
        private readonly bool   $verifySsl       = true,
        private readonly ?string $gatewayKey     = null, // PowerTranz-GatewayKey (optional)
        ?string $baseUrl = null,
        private readonly ?LoggerInterface $logger = null,
        /** Additional connection-layer retries when {@see \PowerTranz\Exceptions\ApiConnectionException} is thrown. */
        private readonly int $maxConnectionRetries = 0,
        private readonly int $retryBaseDelayMs = 250,
        private readonly float $retryBackoffMultiplier = 2.0,
        private readonly int $retryMaxDelayMs = 10_000,
    ) {
        if (empty(trim($powerId)))       throw new ConfigurationException('powerId cannot be empty.');
        if (empty(trim($powerPassword))) throw new ConfigurationException('powerPassword cannot be empty.');

        $this->baseUrl = $baseUrl ?? ($sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL);
    }

    /**
     * Build config from an associative array (e.g. Laravel `config/powertranz.php`).
     *
     * Keys: power_id, power_password, sandbox, timeout, connect_timeout, verify_ssl, gateway_key, base_url,
     * retry_max_attempts, retry_base_delay_ms, retry_backoff_multiplier, retry_max_delay_ms
     * (PSR-3 logger cannot be set via array — pass {@see LoggerInterface} to the constructor in code.)
     */
    public static function fromArray(array $c): self
    {
        return new self(
            powerId: (string) ($c['power_id'] ?? ''),
            powerPassword: (string) ($c['power_password'] ?? ''),
            sandbox: (bool) ($c['sandbox'] ?? false),
            timeout: (int) ($c['timeout'] ?? 30),
            connectTimeout: (int) ($c['connect_timeout'] ?? 10),
            verifySsl: (bool) ($c['verify_ssl'] ?? true),
            gatewayKey: (!empty($c['gateway_key'] ?? null)) ? (string) $c['gateway_key'] : null,
            baseUrl: isset($c['base_url']) ? (string) $c['base_url'] : null,
            logger: null,
            maxConnectionRetries: (int) ($c['retry_max_attempts'] ?? 0),
            retryBaseDelayMs: (int) ($c['retry_base_delay_ms'] ?? 250),
            retryBackoffMultiplier: (float) ($c['retry_backoff_multiplier'] ?? 2.0),
            retryMaxDelayMs: (int) ($c['retry_max_delay_ms'] ?? 10_000),
        );
    }

    public function getPowerId(): string      { return $this->powerId; }
    public function getPowerPassword(): string { return $this->powerPassword; }
    public function getBaseUrl(): string      { return $this->baseUrl; }
    public function isSandbox(): bool         { return $this->sandbox; }
    public function getTimeout(): int         { return $this->timeout; }
    public function getConnectTimeout(): int  { return $this->connectTimeout; }
    public function shouldVerifySsl(): bool   { return $this->verifySsl; }
    public function getGatewayKey(): ?string  { return $this->gatewayKey; }
    public function getLogger(): ?LoggerInterface { return $this->logger; }
    /** Additional attempts after the first failed connection (total tries = 1 + this value). */
    public function getMaxConnectionRetries(): int { return $this->maxConnectionRetries; }
    public function getRetryBaseDelayMs(): int { return $this->retryBaseDelayMs; }
    public function getRetryBackoffMultiplier(): float { return $this->retryBackoffMultiplier; }
    public function getRetryMaxDelayMs(): int { return $this->retryMaxDelayMs; }

    public function getAuthHeaders(): array
    {
        $headers = [
            'PowerTranz-PowerTranzId'       => $this->powerId,
            'PowerTranz-PowerTranzPassword'  => $this->powerPassword,
        ];
        if ($this->gatewayKey !== null) {
            $headers['PowerTranz-GatewayKey'] = $this->gatewayKey;
        }
        return $headers;
    }
}
