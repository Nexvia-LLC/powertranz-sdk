<?php

declare(strict_types=1);

namespace PowerTranz\Tests\Integration;

use PHPUnit\Framework\TestCase;
use PowerTranz\Client\HttpClient;
use PowerTranz\Enums\Operation;
use PowerTranz\Exceptions\ApiResponseException;
use PowerTranz\PowerTranzConfig;

/**
 * Hits PowerTranz when POWERTRANZ_INTEGRATION=1 and credentials are set.
 *
 * Uses GET /api/alive directly so failures surface the real exception (unlike {@see \PowerTranz\PowerTranzClient::isAlive()} which hides errors).
 *
 * @group integration
 */
final class SandboxIntegrationTest extends TestCase
{
    private static function integrationEnabled(): bool
    {
        return getenv('POWERTRANZ_INTEGRATION') === '1'
            || getenv('POWERTRANZ_INTEGRATION') === 'true';
    }

    private static function requireIntegrationConfig(): PowerTranzConfig
    {
        if (!self::integrationEnabled()) {
            self::markTestSkipped('Set POWERTRANZ_INTEGRATION=1 to run sandbox integration tests.');
        }

        $cfg = self::credentials();
        if ($cfg === null) {
            self::markTestSkipped('Set POWERTRANZ_POWER_ID and POWERTRANZ_POWER_PASSWORD for integration tests.');
        }

        return $cfg;
    }

    private static function credentials(): ?PowerTranzConfig
    {
        $id = getenv('POWERTRANZ_POWER_ID') ?: '';
        $pw = getenv('POWERTRANZ_POWER_PASSWORD') ?: '';
        if ($id === '' || $pw === '') {
            return null;
        }

        $sandbox = getenv('POWERTRANZ_SANDBOX');
        $isSandbox = $sandbox === false || $sandbox === '' || filter_var($sandbox, FILTER_VALIDATE_BOOL);

        return PowerTranzConfig::fromArray([
            'power_id' => $id,
            'power_password' => $pw,
            'sandbox' => $isSandbox,
            'verify_ssl' => filter_var(getenv('POWERTRANZ_VERIFY_SSL') ?: '1', FILTER_VALIDATE_BOOL),
            'base_url' => getenv('POWERTRANZ_BASE_URL') ?: null,
            'gateway_key' => getenv('POWERTRANZ_GATEWAY_KEY') ?: null,
        ]);
    }

    public function testAliveAgainstSandbox(): void
    {
        $cfg = self::requireIntegrationConfig();

        $http = new HttpClient($cfg);
        try {
            $body = $http->get(Operation::ALIVE);
        } catch (\Throwable $e) {
            self::fail(self::aliveFailureExplanation($cfg, $e));
        }

        self::assertIsArray($body, 'GET /api/alive must return JSON decoded as an array.');
    }

    /**
     * Actionable PHPUnit message when GET /api/alive fails.
     */
    private static function aliveFailureExplanation(PowerTranzConfig $cfg, \Throwable $e): string
    {
        $base = $cfg->getBaseUrl();
        $sandbox = $cfg->isSandbox() ? 'true' : 'false';

        $detail = $e::class . ': ' . $e->getMessage();
        if ($e instanceof ApiResponseException) {
            $detail .= sprintf(
                "\nHTTP status: %d\nResponse body: %s",
                $e->getHttpStatusCode(),
                $e->getResponseBody()
            );
        }

        return <<<TXT
PowerTranz GET /api/alive failed.

{$detail}

--- What you must have ---
1. Merchant credentials: export POWERTRANZ_POWER_ID and POWERTRANZ_POWER_PASSWORD (staging vs production keys must match the host).
2. Opt in: POWERTRANZ_INTEGRATION=1 or true
3. This test is calling base URL "{$base}" (sandbox flag in config: {$sandbox}).

--- If credentials are correct but it still fails ---
- Wrong environment: staging keys must use sandbox URL (default when sandbox=true). Production keys usually need sandbox=false AND the production API base PowerTranz gives you; set POWERTRANZ_BASE_URL if it differs from the SDK default.
- Optional merchant header: set POWERTRANZ_GATEWAY_KEY if your onboarding says so.
- TLS / corporate firewall: POWERTRANZ_VERIFY_SSL=1 (default); only set 0 locally for debugging expired intercept certs — not for production.
- Network: your machine must reach "{$base}" over HTTPS (try curl {$base}/api/alive from the same shell).

TXT;
    }
}
