<?php

declare(strict_types=1);

namespace PowerTranz\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use PowerTranz\Enums\Operation;
use PowerTranz\Exceptions\ApiConnectionException;
use PowerTranz\Exceptions\ApiResponseException;
use PowerTranz\Exceptions\InvalidResponseException;
use PowerTranz\PowerTranzConfig;
use PowerTranz\Response\TransactionResponse;
use PowerTranz\Support\SensitiveDataRedactor;
use Psr\Log\LogLevel;

/**
 * Internal HTTP client.
 *
 * Internal HTTPS transport for PowerTranz REST endpoints.
 * Correctly omits auth headers for the /payment endpoint per spec.
 * Supports optional PowerTranz-GatewayKey header when configured.
 */
final class HttpClient
{
    private Client $guzzle;
    private SensitiveDataRedactor $redactor;

    public function __construct(
        private readonly PowerTranzConfig $config,
        ?SensitiveDataRedactor $redactor = null,
    ) {
        $this->redactor = $redactor ?? new SensitiveDataRedactor();
        $this->guzzle = new Client([
            'base_uri'        => rtrim($this->config->getBaseUrl(), '/'),
            'timeout'         => $this->config->getTimeout(),
            'connect_timeout' => $this->config->getConnectTimeout(),
            'verify'          => $this->config->shouldVerifySsl(),
        ]);
    }

    public function post(Operation $operation, array $payload, ?string $idempotencyKey = null): TransactionResponse
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        if ($operation->requiresAuthHeaders()) {
            $headers = array_merge($headers, $this->config->getAuthHeaders());
        }

        $headers = $this->withIdempotency($headers, $idempotencyKey);

        $path = $this->resolvePath($operation);

        return $this->executeWithRetry(function () use ($path, $headers, $payload): TransactionResponse {
            $this->logRequest('POST', $path, $headers, ['json' => $payload]);

            try {
                $response = $this->guzzle->post($path, [
                    'headers' => $headers,
                    'json'    => $payload,
                ]);

                $body = (string) $response->getBody();
                $this->logResponse($path, $response->getStatusCode(), $body);

                return $this->parseResponse($body);
            } catch (ConnectException $e) {
                throw new ApiConnectionException($e->getMessage(), 0, $e);
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $r = $e->getResponse();
                    throw new ApiResponseException($r->getStatusCode(), (string) $r->getBody(), $e);
                }
                throw new ApiConnectionException($e->getMessage(), 0, $e);
            }
        });
    }

    /**
     * Special POST for /payment — body is just a quoted SpiToken string, not JSON object.
     * Auth headers are explicitly omitted.
     */
    public function postSpiToken(Operation $operation, string $spiToken, ?string $idempotencyKey = null): TransactionResponse
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
        $headers = $this->withIdempotency($headers, $idempotencyKey);

        $path = $this->resolvePath($operation);
        $encoded = json_encode($spiToken, JSON_THROW_ON_ERROR);

        return $this->executeWithRetry(function () use ($path, $headers, $spiToken, $encoded): TransactionResponse {
            $this->logRequest('POST', $path, $headers, ['body' => $encoded]);

            try {
                $response = $this->guzzle->post($path, [
                    'headers' => $headers,
                    'body' => json_encode($spiToken, JSON_THROW_ON_ERROR),
                ]);

                $body = (string) $response->getBody();
                $this->logResponse($path, $response->getStatusCode(), $body);

                return $this->parseResponse($body);
            } catch (ConnectException $e) {
                throw new ApiConnectionException($e->getMessage(), 0, $e);
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $r = $e->getResponse();
                    throw new ApiResponseException($r->getStatusCode(), (string) $r->getBody(), $e);
                }
                throw new ApiConnectionException($e->getMessage(), 0, $e);
            }
        });
    }

    public function get(Operation $operation, array $queryParams = [], array $pathParams = [], ?string $idempotencyKey = null): array
    {
        $path = $this->resolvePath($operation, $pathParams);
        if (!empty($queryParams)) {
            $path .= '?' . http_build_query($queryParams);
        }

        $headers = array_merge(
            ['Accept' => 'application/json'],
            $this->config->getAuthHeaders()
        );
        $headers = $this->withIdempotency($headers, $idempotencyKey);

        return $this->executeWithRetry(function () use ($path, $headers): array {
            $this->logRequest('GET', $path, $headers, []);

            try {
                $response = $this->guzzle->get($path, [
                    'headers' => $headers,
                ]);

                $body = (string) $response->getBody();
                $this->logResponse($path, $response->getStatusCode(), $body);

                return json_decode($body, true, 512, JSON_THROW_ON_ERROR) ?? [];
            } catch (ConnectException $e) {
                throw new ApiConnectionException($e->getMessage(), 0, $e);
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $r = $e->getResponse();
                    throw new ApiResponseException($r->getStatusCode(), (string) $r->getBody(), $e);
                }
                throw new ApiConnectionException($e->getMessage(), 0, $e);
            }
        });
    }

    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    private function executeWithRetry(callable $operation): mixed
    {
        $logger = $this->config->getLogger();
        $max = $this->config->getMaxConnectionRetries();

        return ConnectionRetryPolicy::run(
            $max,
            $this->config->getRetryBaseDelayMs(),
            $this->config->getRetryBackoffMultiplier(),
            $this->config->getRetryMaxDelayMs(),
            function () use ($operation, $logger): mixed {
                try {
                    return $operation();
                } catch (ApiConnectionException $e) {
                    if ($logger !== null) {
                        $logger->log(LogLevel::DEBUG, 'PowerTranz connection attempt failed: ' . $e->getMessage());
                    }
                    throw $e;
                }
            },
        );
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    private function withIdempotency(array $headers, ?string $idempotencyKey): array
    {
        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        return $headers;
    }

    /**
     * @param array<string, mixed>|array{json: array<string, mixed>}|array{body: string} $payloadHint
     */
    private function logRequest(string $method, string $path, array $headers, array $payloadHint): void
    {
        $logger = $this->config->getLogger();
        if ($logger === null) {
            return;
        }

        $safeHeaders = $this->redactor->redactHeaders($headers);

        $context = [
            'method'  => $method,
            'path'    => $path,
            'headers' => $safeHeaders,
        ];

        if (isset($payloadHint['json']) && is_array($payloadHint['json'])) {
            $context['body'] = $this->redactor->redactArray($payloadHint['json']);
        } elseif (isset($payloadHint['body'])) {
            $ctx = $this->redactor->redactString((string) $payloadHint['body']);
            try {
                $context['body'] = json_decode($ctx, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $context['body'] = $ctx;
            }
        }

        $logger->log(LogLevel::DEBUG, 'PowerTranz HTTP request', $context);
    }

    private function logResponse(string $path, int $status, string $body): void
    {
        $logger = $this->config->getLogger();
        if ($logger === null) {
            return;
        }

        $snippet = $body;
        if (strlen($snippet) > 8192) {
            $snippet = substr($snippet, 0, 8192) . '…';
        }

        $decoded = null;
        try {
            $decoded = json_decode($snippet, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $decoded = null;
        }

        $safe = is_array($decoded)
            ? $this->redactor->redactArray($decoded)
            : $this->redactor->redactString($snippet);

        $logger->log(LogLevel::DEBUG, 'PowerTranz HTTP response', [
            'path'   => $path,
            'status' => $status,
            'body'   => $safe,
        ]);
    }

    private function resolvePath(Operation $operation, array $pathParams = []): string
    {
        $path = $operation->value;
        foreach ($pathParams as $k => $v) {
            $path = str_replace('{' . $k . '}', urlencode((string) $v), $path);
        }
        return $path;
    }

    private function parseResponse(string $body): TransactionResponse
    {
        if (empty($body)) {
            throw new InvalidResponseException('(empty body)');
        }

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidResponseException($body, $e);
        }

        if (!is_array($decoded)) {
            throw new InvalidResponseException($body);
        }

        return TransactionResponse::fromArray($decoded);
    }
}
