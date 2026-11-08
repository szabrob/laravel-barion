<?php

declare(strict_types=1);

namespace Szabrob\LaravelBarion\Enums;

enum Locale: string
{
    case Hungarian = 'hu-HU';
    case English = 'en-US';
    case German = 'de-DE';
    case Slovenian = 'sl-SI';
    case Slovak = 'sk-SK';
    case French = 'fr-FR';
    case Czech = 'cs-CZ';
    case Greek = 'el-GR';
    case Spanish = 'es-ES';
    case Croatian = 'hr-HR';
    case Romanian = 'ro-RO';
    case Polish = 'pl-PL';
    case Italian = 'it-IT';
    case Bulgarian = 'bg-BG';
}
