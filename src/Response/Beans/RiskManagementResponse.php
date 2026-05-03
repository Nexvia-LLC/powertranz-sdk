<?php

declare(strict_types=1);

namespace PowerTranz\Response\Beans;

use PowerTranz\Enums\AuthenticationStatus;
use PowerTranz\Enums\FraudCheckResponseCode;
use PowerTranz\Request\Beans\Address;

/**
 * 3-D Secure result block from the gateway (eci, cavv, xid, authentication status,
 * redirectData, authenticateUrl, cardEnrolled, protocol version, etc.).
 */
class ThreeDSecureResponse
{
    public function __construct(
        public readonly ?string               $eci,
        public readonly ?string               $cavv,
        public readonly ?string               $xid,
        public readonly ?AuthenticationStatus $authenticationStatus,
        public readonly ?string               $authenticationStatusRaw,
        public readonly ?string               $statusReason,
        public readonly ?string               $redirectData,
        public readonly ?string               $authenticateUrl,
        public readonly ?string               $cardEnrolled,
        public readonly ?string               $protocolVersion,
        public readonly ?string               $fingerprintIndicator,
        public readonly ?string               $dsTransId,
        public readonly ?string               $responseCode,
        public readonly ?string               $cardholderInfo,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = isset($data['AuthenticationStatus'])
            ? AuthenticationStatus::tryFrom($data['AuthenticationStatus'])
            : null;

        return new self(
            eci: $data['Eci'] ?? null,
            cavv: $data['Cavv'] ?? null,
            xid: $data['Xid'] ?? null,
            authenticationStatus: $status,
            authenticationStatusRaw: $data['AuthenticationStatus'] ?? null,
            statusReason: $data['StatusReason'] ?? null,
            redirectData: $data['RedirectData'] ?? null,
            authenticateUrl: $data['AuthenticateUrl'] ?? null,
            cardEnrolled: $data['CardEnrolled'] ?? null,
            protocolVersion: $data['ProtocolVersion'] ?? null,
            fingerprintIndicator: $data['FingerprintIndicator'] ?? null,
            dsTransId: $data['DsTransId'] ?? null,
            responseCode: $data['ResponseCode'] ?? null,
            cardholderInfo: $data['CardholderInfo'] ?? null,
        );
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticationStatus?->isSuccessful() ?? false;
    }

    /**
     * MUST display this message to cardholder if present — per PowerTranz spec.
     */
    public function hasCardholderInfoMessage(): bool
    {
        return !empty($this->cardholderInfo);
    }
}

/**
 * Fraud-check result block (e.g. provider integration such as Kount).
 */
class FraudCheckResponse
{
    public function __construct(
        public readonly ?string                  $fcProvider,
        public readonly ?string                  $responseCode,
        public readonly ?FraudCheckResponseCode  $fcResponseCode,
        public readonly ?string                  $fcResponseCodeRaw,
        public readonly ?string                  $fcScore,
        public readonly ?string                  $fcTransId,
        public readonly array                    $fcDetails = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $code = isset($data['FcResponseCode'])
            ? FraudCheckResponseCode::tryFrom($data['FcResponseCode'])
            : null;

        return new self(
            fcProvider: $data['FcProvider'] ?? null,
            responseCode: $data['ResponseCode'] ?? null,
            fcResponseCode: $code,
            fcResponseCodeRaw: $data['FcResponseCode'] ?? null,
            fcScore: $data['FcScore'] ?? null,
            fcTransId: $data['FcTransId'] ?? null,
            fcDetails: $data['FcDetails'] ?? [],
        );
    }

    public function isSafe(): bool  { return $this->fcResponseCode?->isSafe() ?? false; }
    public function isFraud(): bool { return $this->fcResponseCode?->isFraud() ?? false; }
    public function getScore(): ?int { return $this->fcScore !== null ? (int) $this->fcScore : null; }
}

/**
 * Risk management block: AVS/CVV codes plus nested 3DS and fraud-check sub-responses.
 */
class RiskManagementResponse
{
    public function __construct(
        public readonly ?string               $avsResponseCode,
        public readonly ?string               $cvvResponseCode,
        public readonly ?ThreeDSecureResponse $threeDSecure,
        public readonly ?FraudCheckResponse   $fraudCheck,
    ) {}

    public static function fromArray(array $data): self
    {
        $tds = isset($data['ThreeDSecure']) ? ThreeDSecureResponse::fromArray($data['ThreeDSecure']) : null;
        $fc  = isset($data['FraudCheck'])   ? FraudCheckResponse::fromArray($data['FraudCheck'])     : null;

        return new self(
            avsResponseCode: $data['AvsResponseCode'] ?? null,
            cvvResponseCode: $data['CvvResponseCode'] ?? null,
            threeDSecure: $tds,
            fraudCheck: $fc,
        );
    }
}
