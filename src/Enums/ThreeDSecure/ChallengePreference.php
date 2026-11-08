<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Enums\ThreeDSecure;

enum ChallengePreference: string
{
    case NoPreference = 'NoPreference';
    case ChallengeRequired = 'ChallengeRequired';
    case NoChallengeNeeded = 'NoChallengeNeeded';
}
