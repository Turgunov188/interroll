<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Material: string implements HasLabel
{
    case Polyurethane = 'pu';
    case Rubber = 'rubber';
    case Silicone = 'silicone';
    case Other = 'other';

    /** Short code the catalog page filters by (its PU / rubber toggles). */
    public function filterCode(): string
    {
        return match ($this) {
            self::Polyurethane => 'pu',
            self::Rubber => 'rz',
            self::Silicone => 'si',
            self::Other => 'ot',
        };
    }

    public function label(?Locale $locale = null): string
    {
        return match ($locale ?? Locale::main()) {
            Locale::Ru => match ($this) {
                self::Polyurethane => 'Полиуретан',
                self::Rubber => 'Резина',
                self::Silicone => 'Силикон',
                self::Other => 'Другое',
            },
            Locale::Uz => match ($this) {
                self::Polyurethane => 'Poliuretan',
                self::Rubber => 'Rezina',
                self::Silicone => 'Silikon',
                self::Other => 'Boshqa',
            },
            Locale::En => match ($this) {
                self::Polyurethane => 'Polyurethane',
                self::Rubber => 'Rubber',
                self::Silicone => 'Silicone',
                self::Other => 'Other',
            },
            Locale::Tr => match ($this) {
                self::Polyurethane => 'Poliüretan',
                self::Rubber => 'Kauçuk',
                self::Silicone => 'Silikon',
                self::Other => 'Diğer',
            },
        };
    }

    /** Cast parts (polyurethane, rubber): the product page shows their working temperature and «why polyurethane». */
    public function isCastElastomer(): bool
    {
        return in_array($this, [self::Polyurethane, self::Rubber], true);
    }

    /** Admin panel label (Russian). */
    public function getLabel(): string
    {
        return $this->label(Locale::Ru);
    }
}
