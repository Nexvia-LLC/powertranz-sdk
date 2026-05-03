<?php declare(strict_types=1);

namespace PowerTranz\Enums;

enum ChallengeIndicator: string
{
    case NoPreference         = '01';
    case NoChallengeRequested = '02';
    case ChallengeRequested   = '03';
    case ChallengeMandated    = '04';
}
