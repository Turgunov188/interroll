<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Which form on the site created the lead (set per page by <meta name="ir-lead-type">). */
enum LeadType: string implements HasLabel
{
    case Quote = 'quote';
    case Recoating = 'recoating';
    case Wizard = 'wizard';
    case Callback = 'callback';
    case Contact = 'contact';

    public function getLabel(): string
    {
        return match ($this) {
            self::Quote => 'Запрос цены',
            self::Recoating => 'Восстановление покрытия',
            self::Wizard => 'Мастер подбора',
            self::Callback => 'Обратный звонок',
            self::Contact => 'Форма контактов',
        };
    }
}
