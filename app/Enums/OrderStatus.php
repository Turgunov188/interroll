<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** The steps a customer sees on the tracking page, in order. */
enum OrderStatus: string implements HasColor, HasLabel
{
    case Accepted = 'accepted';
    case Engineering = 'engineering';
    case Casting = 'casting';
    case Machining = 'machining';
    case QualityCheck = 'quality';
    case Ready = 'ready';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /** Label for the public tracking page in the visitor's language. */
    public function label(?Locale $locale = null): string
    {
        $uz = [
            'accepted' => 'Qabul qilindi',
            'engineering' => 'Muhandislik',
            'casting' => 'Quyish',
            'machining' => 'Mexanik ishlov',
            'quality' => 'Sifat nazorati',
            'ready' => 'Tayyor',
            'delivered' => 'Topshirildi',
            'cancelled' => 'Bekor qilindi',
        ];
        $ru = [
            'accepted' => 'Принят',
            'engineering' => 'Инженерия',
            'casting' => 'Литьё',
            'machining' => 'Мехобработка',
            'quality' => 'Контроль качества',
            'ready' => 'Готов',
            'delivered' => 'Выдан',
            'cancelled' => 'Отменён',
        ];

        $en = [
            'accepted' => 'Accepted',
            'engineering' => 'Engineering',
            'casting' => 'Casting',
            'machining' => 'Machining',
            'quality' => 'Quality control',
            'ready' => 'Ready',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];
        $tr = [
            'accepted' => 'Kabul edildi',
            'engineering' => 'Mühendislik',
            'casting' => 'Döküm',
            'machining' => 'Talaşlı işleme',
            'quality' => 'Kalite kontrol',
            'ready' => 'Hazır',
            'delivered' => 'Teslim edildi',
            'cancelled' => 'İptal edildi',
        ];

        return (match ($locale ?? Locale::main()) {
            Locale::Ru => $ru,
            Locale::Uz => $uz,
            Locale::En => $en,
            Locale::Tr => $tr,
        })[$this->value];
    }

    public function getLabel(): string
    {
        return $this->label(Locale::Ru);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Accepted => 'gray',
            self::Engineering, self::Casting, self::Machining, self::QualityCheck => 'info',
            self::Ready => 'warning',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }

    /** Steps shown as the progress line (cancelled is not a step). */
    public static function steps(): array
    {
        return array_values(array_filter(self::cases(), fn (self $s) => $s !== self::Cancelled));
    }
}
