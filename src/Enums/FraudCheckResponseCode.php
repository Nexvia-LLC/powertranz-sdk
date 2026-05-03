<?php declare(strict_types=1);

namespace PowerTranz\Enums;

enum FraudCheckResponseCode: string
{
    case Approved = 'A';
    case Declined = 'D';
    case Review   = 'R';
    case Escalate = 'E';

    public function isSafe(): bool   { return $this === self::Approved; }
    public function isFraud(): bool  { return $this === self::Declined; }
}
