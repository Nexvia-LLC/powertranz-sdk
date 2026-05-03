<?php declare(strict_types=1);

namespace PowerTranz\Enums;

enum AuthenticationStatus: string
{
    case Authenticated    = 'Y';
    case NotAuthenticated = 'N';
    case Attempted        = 'A';
    case Unavailable      = 'U';
    case Rejected         = 'R';
    case Informational    = 'I';

    public function isSuccessful(): bool
    {
        return in_array($this, [self::Authenticated, self::Attempted], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Authenticated    => 'Authenticated',
            self::NotAuthenticated => 'Not Authenticated',
            self::Attempted        => 'Attempted',
            self::Unavailable      => 'Unavailable',
            self::Rejected         => 'Rejected',
            self::Informational    => 'Informational Only',
        };
    }
}
