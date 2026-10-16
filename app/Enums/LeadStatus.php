<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LeadStatus: string implements HasColor, HasLabel
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Quoted = 'quoted';       // narx yuborildi
    case Won = 'won';             // buyurtmaga aylandi
    case Lost = 'lost';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'Новая',
            self::InProgress => 'В работе',
            self::Quoted => 'КП отправлено',
            self::Won => 'Заказ',
            self::Lost => 'Отказ',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::InProgress => 'info',
            self::Quoted => 'primary',
            self::Won => 'success',
            self::Lost => 'gray',
        };
    }
}
