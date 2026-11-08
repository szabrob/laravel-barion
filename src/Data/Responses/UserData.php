<?php

declare(strict_types=1);

namespace LaravelBarion\LaravelBarion\Data\Responses;

use LaravelBarion\LaravelBarion\Data\Concerns\IgnoresNullInput;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;

#[MapInputName(StudlyCaseMapper::class)]
final class UserData extends Data
{
    use IgnoresNullInput;

    public function __construct(
        public readonly ?UserNameData $name = null,
        public readonly ?string $email = null,
    ) {}
}
