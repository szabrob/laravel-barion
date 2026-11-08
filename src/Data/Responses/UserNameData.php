<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Responses;

use LaravelBarion\LaravelBarion\Data\Concerns\IgnoresNullInput;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
final class UserNameData extends Data
{
    use IgnoresNullInput;

    public function __construct(
        public readonly ?string $loginName = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $organizationName = null,
    ) {}

    /**
     * The organization name, or the full name, or the login name, whichever is available first.
     */
    public function displayName(): ?string
    {
        $fullName = trim(($this->firstName ?? '').' '.($this->lastName ?? ''));

        return $this->organizationName ?? ($fullName !== '' ? $fullName : $this->loginName);
    }
}
