<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Material: string implements HasLabel
{
    case Polyurethane = 'pu';
    case Rubber = 'rubber';
    case Other = 'other';

    /** Short code the catalog page filters by (its PU / rubber toggles). */
    public function filterCode(): string
    {
        return match ($this) {
            self::Polyurethane => 'pu',
            self::Rubber => 'rz',
            self::Other => 'ot',
        };
    }

    public function label(?Locale $locale = null): string
    {
        return match ($locale ?? Locale::main()) {
            Locale::Ru => match ($this) {
                self::Polyurethane => 'Полиуретан',
                self::Rubber => 'Резина',
                self::Other => 'Другое',
            },
            Locale::Uz => match ($this) {
                self::Polyurethane => 'Poliuretan',
                self::Rubber => 'Rezina',
                self::Other => 'Boshqa',
            },
            Locale::En => match ($this) {
                self::Polyurethane => 'Polyurethane',
                self::Rubber => 'Rubber',
                self::Other => 'Other',
            },
            Locale::Tr => match ($this) {
                self::Polyurethane => 'Poliüretan',
                self::Rubber => 'Kauçuk',
                self::Other => 'Diğer',
            },
        };
    }

    /** Admin panel label (Russian). */
    public function getLabel(): string
    {
        return $this->label(Locale::Ru);
    }
}
