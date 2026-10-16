<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Site languages. Russian is the main one: it has no URL prefix,
 * it is required in translatable fields, and other languages fall back to it.
 */
enum Locale: string implements HasLabel
{
    case Ru = 'ru';
    case Uz = 'uz';
    case En = 'en';
    case Tr = 'tr';

    public static function main(): self
    {
        return self::Ru;
    }

    public function isMain(): bool
    {
        return $this === self::main();
    }

    /** URL of a site page in this language: "/catalog" → "/catalog" (ru), "/uz/catalog" (uz). */
    public function path(string $uri): string
    {
        if ($this->isMain()) {
            return $uri;
        }

        return rtrim('/'.$this->value.$uri, '/');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Ru => 'Русский',
            self::Uz => "O'zbekcha",
            self::En => 'English',
            self::Tr => 'Türkçe',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
