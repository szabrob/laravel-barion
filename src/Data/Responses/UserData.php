<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Data\Responses;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Szabrob\LaravelBarion\Data\Concerns\IgnoresNullInput;

#[MapInputName(StudlyCaseMapper::class)]
final class UserData extends Data
{
    use IgnoresNullInput;

    public function __construct(
        public readonly ?UserNameData $name = null,
        public readonly ?string $email = null,
    ) {}
}
