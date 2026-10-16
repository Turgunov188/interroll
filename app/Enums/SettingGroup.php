<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Tabs of the «Настройки сайта» page. */
enum SettingGroup: string implements HasLabel
{
    case Contacts = 'contacts';
    case Hours = 'hours';
    case Directions = 'directions';
    case Staff = 'staff';
    case Requisites = 'requisites';
    case Production = 'production';
    case Recoating = 'recoating';
    case Factory = 'factory';
    case Terms = 'terms';
    case Images = 'images';

    public function getLabel(): string
    {
        return match ($this) {
            self::Contacts => 'Контакты',
            self::Hours => 'Режим работы',
            self::Directions => 'Как добраться',
            self::Staff => 'Сотрудники',
            self::Requisites => 'Реквизиты',
            self::Production => 'Производство',
            self::Recoating => 'Восстановление',
            self::Factory => 'Завод',
            self::Terms => 'Условия',
            self::Images => 'Фото',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Contacts => 'heroicon-o-phone',
            self::Hours => 'heroicon-o-clock',
            self::Directions => 'heroicon-o-map-pin',
            self::Staff => 'heroicon-o-user-group',
            self::Requisites => 'heroicon-o-building-library',
            self::Production => 'heroicon-o-cog-6-tooth',
            self::Recoating => 'heroicon-o-arrow-path',
            self::Factory => 'heroicon-o-building-office',
            self::Terms => 'heroicon-o-document-text',
            self::Images => 'heroicon-o-photo',
        };
    }
}
