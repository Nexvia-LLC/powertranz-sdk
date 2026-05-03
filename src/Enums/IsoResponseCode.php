<?php

declare(strict_types=1);

namespace PowerTranz\Enums;

/**
 * ISO Response Codes returned by the PowerTranz gateway.
 */
enum IsoResponseCode: string
{
    case Approved                 = '00';
    case SpiPreprocessingComplete = 'SP4';
    case ThreeDsNotSupported      = 'SP1';
    case ThreeDsComplete          = '3D0';
    case ThreeDsAttempted         = '3D1';
    case ThreeDsFailed            = '3D2';
    case ThreeDsUnavailable       = '3D3';
    case HppPreprocessingComplete = 'HP0';
    case FraudCheckApproved       = 'FC0';
    case FraudCheckDeclined       = 'FC1';
    case DoNotHonour              = '05';
    case InvalidTransaction       = '12';
    case InvalidAmount            = '13';
    case InvalidCardNumber        = '14';
    case InsufficientFunds        = '51';
    case ExpiredCard              = '54';
    case InvalidCvv               = '82';
    case CardDeclined             = '91';
    /** Merchant / request authentication failure (e.g. credentials, gateway key, or invalid callback URL). */
    case FailedAuthentication     = '89';

    public function isApproved(): bool         { return $this === self::Approved; }
    public function isSpiPreprocessing(): bool  { return $this === self::SpiPreprocessingComplete; }
    public function isHppComplete(): bool       { return $this === self::HppPreprocessingComplete; }
    public function isThreeDsComplete(): bool
    {
        return in_array($this, [self::ThreeDsComplete, self::ThreeDsAttempted, self::ThreeDsNotSupported], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Approved                 => 'Transaction Approved',
            self::SpiPreprocessingComplete => 'SPI Preprocessing Complete',
            self::ThreeDsNotSupported      => '3DS Not Supported',
            self::ThreeDsComplete          => '3DS Authentication Complete',
            self::ThreeDsAttempted         => '3DS Authentication Attempted',
            self::ThreeDsFailed            => '3DS Authentication Failed',
            self::ThreeDsUnavailable       => '3DS Unavailable',
            self::HppPreprocessingComplete => 'HPP Preprocessing Complete',
            self::FraudCheckApproved       => 'Fraud Check Approved',
            self::FraudCheckDeclined       => 'Fraud Check Declined',
            self::DoNotHonour              => 'Do Not Honour',
            self::InvalidTransaction       => 'Invalid Transaction',
            self::InvalidAmount            => 'Invalid Amount',
            self::InvalidCardNumber        => 'Invalid Card Number',
            self::InsufficientFunds        => 'Insufficient Funds',
            self::ExpiredCard              => 'Expired Card',
            self::InvalidCvv               => 'Invalid CVV',
            self::CardDeclined             => 'Card Declined',
            self::FailedAuthentication     => 'Failed authentication',
        };
    }
}
