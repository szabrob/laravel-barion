<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums\ThreeDSecure;

enum ChallengePreference: string
{
    case NoPreference = 'NoPreference';
    case ChallengeRequired = 'ChallengeRequired';
    case NoChallengeNeeded = 'NoChallengeNeeded';
}
