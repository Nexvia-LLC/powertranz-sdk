<?php

declare(strict_types=1);

namespace PowerTranz\Response;

use PowerTranz\Enums\IsoResponseCode;
use PowerTranz\Request\Beans\Address;
use PowerTranz\Response\Beans\RiskManagementResponse;

/**
 * Unified PowerTranz Transaction Response.
 *
 * Represents JSON responses from gateway endpoints such as
 * auth, sale, risk management, payment completion, capture, refund, and void.
 *
 * Historical note — newer fields vs older SDK versions:
 *  - cardSuffix        (last 4 digits — safe to store, replaces raw PAN)
 *  - hostRRN           (host retrieval reference number)
 *  - emvIssuerAuthenticationData
 *  - emvIssuerScripts
 *  - emvResponseCode
 *  - avsResponseCode   (from RiskManagement)
 *  - cvvResponseCode   (from RiskManagement)
 *  - errors[]          (structured error list)
 *  - customData
 *  - host
 */
final class TransactionResponse
{
    public function __construct(
        // Core identification
        public readonly ?int    $transactionType,
        public readonly bool    $approved,
        public readonly ?string $authorizationCode,
        public readonly string  $transactionIdentifier,
        public readonly float   $totalAmount,
        public readonly string  $currencyCode,

        // Response codes
        public readonly ?IsoResponseCode $isoResponseCode,
        public readonly ?string          $isoResponseCodeRaw,
        public readonly string           $responseMessage,

        // Card info (safe tokens only — never raw PAN)
        public readonly ?string $panToken,
        public readonly ?string $cardSuffix,     // Last 4 digits — from CardSuffix
        public readonly ?string $cardBrand,

        // Settlement
        public readonly ?string $rrn,
        public readonly ?string $hostRRN,

        // EMV fields
        public readonly ?string $emvIssuerAuthenticationData,
        public readonly ?string $emvIssuerScripts,
        public readonly ?string $emvResponseCode,

        // SPI flow
        public readonly ?string $spiToken,
        public readonly ?string $redirectData,

        // Order / reference
        public readonly ?string $orderIdentifier,
        public readonly ?string $externalIdentifier,

        // Risk management (3DS + FraudCheck + AVS + CVV)
        public readonly ?RiskManagementResponse $riskManagement,

        // HPP-collected billing address
        public readonly ?Address $billingAddress,

        // Custom / host data
        public readonly mixed  $customData,
        public readonly mixed  $host,
        public readonly array  $errors,

        // Raw payload for debugging
        public readonly array  $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        $isoCode = IsoResponseCode::tryFrom($data['IsoResponseCode'] ?? '');

        $riskMgmt = isset($data['RiskManagement'])
            ? RiskManagementResponse::fromArray($data['RiskManagement'])
            : null;

        $billing = isset($data['BillingAddress'])
            ? \PowerTranz\Request\Beans\Address::fromArray($data['BillingAddress'])
            : null;

        return new self(
            transactionType: isset($data['TransactionType']) ? (int) $data['TransactionType'] : null,
            approved: (bool) ($data['Approved'] ?? false),
            authorizationCode: $data['AuthorizationCode'] ?? null,
            transactionIdentifier: $data['TransactionIdentifier'] ?? '',
            totalAmount: (float) ($data['TotalAmount'] ?? 0),
            currencyCode: $data['CurrencyCode'] ?? '',
            isoResponseCode: $isoCode,
            isoResponseCodeRaw: $data['IsoResponseCode'] ?? null,
            responseMessage: $data['ResponseMessage'] ?? '',
            panToken: $data['PanToken'] ?? null,
            cardSuffix: $data['CardSuffix'] ?? null,
            cardBrand: $data['CardBrand'] ?? null,
            rrn: $data['RRN'] ?? null,
            hostRRN: $data['HostRRN'] ?? null,
            emvIssuerAuthenticationData: $data['EmvIssuerAuthenticationData'] ?? null,
            emvIssuerScripts: $data['EmvIssuerScripts'] ?? null,
            emvResponseCode: $data['EmvResponseCode'] ?? null,
            spiToken: $data['SpiToken'] ?? null,
            redirectData: $data['RedirectData'] ?? null,
            orderIdentifier: $data['OrderIdentifier'] ?? null,
            externalIdentifier: $data['ExternalIdentifier'] ?? null,
            riskManagement: $riskMgmt,
            billingAddress: $billing,
            customData: $data['CustomData'] ?? null,
            host: $data['Host'] ?? null,
            errors: $data['Errors'] ?? [],
            raw: $data,
        );
    }

    // -------------------------------------------------------------------------
    // Flow state helpers
    // -------------------------------------------------------------------------

    public function isSpiPreprocessingComplete(): bool
    {
        return $this->isoResponseCode === IsoResponseCode::SpiPreprocessingComplete;
    }

    public function isThreeDsComplete(): bool
    {
        return $this->isoResponseCode?->isThreeDsComplete() ?? false;
    }

    public function isHppComplete(): bool
    {
        return $this->isoResponseCode === IsoResponseCode::HppPreprocessingComplete;
    }

    public function isApproved(): bool
    {
        return $this->approved && $this->isoResponseCode === IsoResponseCode::Approved;
    }

    public function hasSpiToken(): bool
    {
        return !empty($this->spiToken);
    }

    public function hasRedirectData(): bool
    {
        return !empty($this->redirectData);
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Gate check: is it safe to proceed to payment completion?
     *
     * Returns true when ALL of:
     * - SpiToken is present
     * - 3DS passed (if performed)
     * - Fraud check passed (if performed)
     */
    public function canProceedToPayment(): bool
    {
        if (!$this->hasSpiToken()) return false;

        $tds = $this->riskManagement?->threeDSecure;
        if ($tds !== null && !$tds->isAuthenticated()) return false;

        $fc = $this->riskManagement?->fraudCheck;
        if ($fc !== null && !$fc->isSafe()) return false;

        return true;
    }

    // -------------------------------------------------------------------------
    // Convenience accessors
    // -------------------------------------------------------------------------

    public function getThreeDSecure(): ?\PowerTranz\Response\Beans\ThreeDSecureResponse
    {
        return $this->riskManagement?->threeDSecure;
    }

    public function getFraudCheck(): ?\PowerTranz\Response\Beans\FraudCheckResponse
    {
        return $this->riskManagement?->fraudCheck;
    }

    public function getAvsResponseCode(): ?string
    {
        return $this->riskManagement?->avsResponseCode;
    }

    public function getCvvResponseCode(): ?string
    {
        return $this->riskManagement?->cvvResponseCode;
    }

    /**
     * The last 4 digits of the card — safe to store and display.
     * Prefer this over trying to parse the PAN.
     */
    public function getCardSuffix(): ?string
    {
        return $this->cardSuffix;
    }

    public function getCardDisplay(): string
    {
        if ($this->cardBrand && $this->cardSuffix) {
            return "{$this->cardBrand} ending {$this->cardSuffix}";
        }
        return $this->cardBrand ?? 'Unknown card';
    }
}
