<?php

declare(strict_types=1);

namespace PowerTranz;

use PowerTranz\Client\HttpClient;
use PowerTranz\Enums\Operation;
use PowerTranz\Exceptions\InvalidResponseException;
use PowerTranz\Exceptions\ValidationException;
use PowerTranz\Request\AuthRequest;
use PowerTranz\Request\CaptureRequest;
use PowerTranz\Request\NonfinancialRequest;
use PowerTranz\Request\RefundRequest;
use PowerTranz\Request\VoidRequest;
use PowerTranz\Request\TransactionSearchRequest;
use PowerTranz\Request\SaleRequest;
use PowerTranz\Response\OrderResponse;
use PowerTranz\Response\TransactionResponse;

/**
 * PowerTranz PHP SDK — Main Client
 *
 * Main SDK entry point for PowerTranz REST operations.
 * with full PHP 8.1+ ergonomics.
 *
 * Supports these gateway operations:
 *   ALIVE, AUTH_3DS, SALE_3DS, RISK_MGMT_3DS, PAYMENT_3DS,
 *   AUTH, SALE, RISK, PAYMENT, CAPTURE, REFUND, VOID,
 *   GET_TRANSACTION, SEARCH_TRANSACTIONS
 *
 * ─────────────────────────────────────────────────────────────────
 * QUICK START
 * ─────────────────────────────────────────────────────────────────
 *
 * $config = new PowerTranzConfig('YOUR_ID', 'YOUR_PASSWORD', sandbox: true);
 * $client = new PowerTranzClient($config);
 *
 * // Build a sale with HPP + 3DS + Fraud Check
 * $extData = new ExtendedRequestData();
 * $extData->setMerchantResponseUrl('https://yoursite.com/callback');
 * $extData->setThreeDSecure(new ThreeDSecureRequestData());
 * $extData->setHostedPage(new HostedPageRequestData('PTZ/MySet', 'MyPage'));
 *
 * $sale = new SaleRequest(25.00, '840');
 * $sale->setThreeDSecure(true)->setFraudCheck(true)->setExtendedData($extData);
 *
 * $response = $client->sale($sale);
 * // $response->spiToken, $response->redirectData are now available
 *
 * // After callback:
 * $final = $client->completePayment($response->spiToken);
 */
final class PowerTranzClient
{
    private HttpClient $http;

    public function __construct(private readonly PowerTranzConfig $config)
    {
        $this->http = new HttpClient($config);
    }

    // =========================================================================
    // Health Check
    // =========================================================================

    public function isAlive(): bool
    {
        try {
            $this->http->get(Operation::ALIVE);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    // =========================================================================
    // SPI Initiation (Step 1 of every SPI flow)
    // =========================================================================

    /**
     * SPI Sale — Authorization + Auto-capture with 3DS/HPP/FraudCheck support.
     * Maps to Operation::SALE_3DS (/api/spi/sale)
     */
    public function sale(SaleRequest $request, ?string $idempotencyKey = null): TransactionResponse
    {
        return $this->http->post(Operation::SALE_3DS, $request->toArray(), $idempotencyKey);
    }

    /**
     * SPI Authorization — Funds held, requires manual capture.
     * Maps to Operation::AUTH_3DS (/api/spi/auth)
     */
    public function authorize(AuthRequest $request, ?string $idempotencyKey = null): TransactionResponse
    {
        return $this->http->post(Operation::AUTH_3DS, $request->toArray(), $idempotencyKey);
    }

    /**
     * SPI Risk Management — 3DS auth / tokenization only, no financial transaction.
     * Maps to Operation::RISK_MGMT_3DS (/api/spi/riskmgmt)
     */
    public function riskManagement(NonfinancialRequest $request, ?string $idempotencyKey = null): TransactionResponse
    {
        return $this->http->post(Operation::RISK_MGMT_3DS, $request->toArray(), $idempotencyKey);
    }

    // =========================================================================
    // Payment Completion (Step 2 — after 3DS/HPP iFrame callback)
    // =========================================================================

    /**
     * Complete payment using SpiToken from the callback.
     * Maps to Operation::PAYMENT_3DS (/api/spi/payment)
     *
     * MUST be called within 5 minutes of the original sale/authorize call.
     * Auth headers are intentionally omitted — per PowerTranz spec.
     *
     * @throws ValidationException
     */
    public function completePayment(string $spiToken, ?string $idempotencyKey = null): TransactionResponse
    {
        if (empty(trim($spiToken))) {
            throw new ValidationException('SpiToken cannot be empty for payment completion.');
        }
        return $this->http->postSpiToken(Operation::PAYMENT_3DS, $spiToken, $idempotencyKey);
    }

    /**
     * Convenience: complete payment directly from a prior response object.
     */
    public function completePaymentFromResponse(TransactionResponse $response, ?string $idempotencyKey = null): TransactionResponse
    {
        if (!$response->hasSpiToken()) {
            throw new ValidationException('Cannot complete payment: TransactionResponse has no SpiToken.');
        }
        if (!$response->canProceedToPayment()) {
            throw new ValidationException(
                'Cannot proceed to payment: 3DS failed or fraud check declined. ' .
                'Check response->getThreeDSecure() and response->getFraudCheck().'
            );
        }
        return $this->completePayment($response->spiToken, $idempotencyKey);
    }

    // =========================================================================
    // Post-Authorization Operations
    // =========================================================================

    /**
     * Capture a previously authorized transaction.
     * Maps to Operation::CAPTURE (/api/capture)
     */
    public function capture(CaptureRequest $request, ?string $idempotencyKey = null): TransactionResponse
    {
        return $this->http->post(Operation::CAPTURE, $request->toArray(), $idempotencyKey);
    }

    /**
     * Refund a previously settled transaction (full or partial).
     * Maps to Operation::REFUND (/api/refund)
     */
    public function refund(RefundRequest $request, ?string $idempotencyKey = null): TransactionResponse
    {
        return $this->http->post(Operation::REFUND, $request->toArray(), $idempotencyKey);
    }

    /**
     * Void an authorization before it has been captured.
     * Maps to Operation::VOID (/api/void)
     */
    public function void(VoidRequest $request, ?string $idempotencyKey = null): TransactionResponse
    {
        return $this->http->post(Operation::VOID, $request->toArray(), $idempotencyKey);
    }

    // =========================================================================
    // Transaction Lookup
    // =========================================================================

    /**
     * Retrieve a single transaction by its TransactionIdentifier.
     * Maps to Operation::GET_TRANSACTION (/api/transactions/{transactionId})
     */
    public function getTransaction(string $transactionId, ?string $idempotencyKey = null): TransactionResponse
    {
        if (empty(trim($transactionId))) {
            throw new ValidationException('TransactionIdentifier cannot be empty.');
        }
        $raw = $this->http->get(Operation::GET_TRANSACTION, [], ['transactionId' => $transactionId], $idempotencyKey);
        return TransactionResponse::fromArray($raw);
    }

    /**
     * Search transactions by various criteria.
     * Maps to Operation::SEARCH_TRANSACTIONS (/api/transactions/search)
     *
     * @return OrderResponse[]
     */
    public function searchTransactions(TransactionSearchRequest $request, ?string $idempotencyKey = null): array
    {
        $raw = $this->http->get(Operation::SEARCH_TRANSACTIONS, $request->toQueryParams(), [], $idempotencyKey);

        // The search endpoint returns an array of transaction objects
        if (!isset($raw[0])) {
            return [];
        }

        return array_map(
            static fn(array $item) => OrderResponse::fromArray($item),
            $raw
        );
    }

    // =========================================================================
    // Callback Parsing
    // =========================================================================

    /**
     * Parse the POST body that PowerTranz sends to your MerchantResponseUrl.
     *
     * Use this in your callback controller:
     *   $response = $client->parseCallback(file_get_contents('php://input'));
     *
     * @throws InvalidResponseException
     */
    public function parseCallback(string $rawBody): TransactionResponse
    {
        if (empty(trim($rawBody))) {
            throw new InvalidResponseException('(empty callback body)');
        }
        try {
            $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidResponseException($rawBody, $e);
        }
        return TransactionResponse::fromArray($data);
    }

    // =========================================================================
    // Non-SPI (standard) endpoints — for merchants not using SPI
    // =========================================================================

    /** Standard (non-SPI) Sale. Maps to /api/sale */
    public function standardSale(SaleRequest $request, ?string $idempotencyKey = null): TransactionResponse
    {
        return $this->http->post(Operation::SALE, $request->toArray(), $idempotencyKey);
    }

    /** Standard (non-SPI) Authorization. Maps to /api/auth */
    public function standardAuthorize(AuthRequest $request, ?string $idempotencyKey = null): TransactionResponse
    {
        return $this->http->post(Operation::AUTH, $request->toArray(), $idempotencyKey);
    }

    /** Standard (non-SPI) Payment completion. Maps to /api/payment */
    public function standardCompletePayment(string $spiToken, ?string $idempotencyKey = null): TransactionResponse
    {
        return $this->http->postSpiToken(Operation::PAYMENT, $spiToken, $idempotencyKey);
    }

    public function getConfig(): PowerTranzConfig
    {
        return $this->config;
    }
}
