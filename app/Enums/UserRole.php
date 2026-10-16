<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Owner = 'owner';   // sees everything, manages staff
    case Admin = 'admin';   // catalog, content, all leads/orders
    case Sales = 'sales';   // leads and orders assigned to them (or unassigned)

    public function getLabel(): string
    {
        return match ($this) {
            self::Owner => 'Руководитель',
            self::Admin => 'Администратор',
            self::Sales => 'Менеджер по продажам',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Owner => 'danger',
            self::Admin => 'warning',
            self::Sales => 'info',
        };
    }
}
