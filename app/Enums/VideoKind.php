<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Where a video goes on /video: the main player with its playlist, or the short-videos strip. */
enum VideoKind: string implements HasLabel
{
    case Main = 'main';
    case Short = 'short';

    public function getLabel(): string
    {
        return match ($this) {
            self::Main => 'Видео (плеер и плейлист)',
            self::Short => 'Короткое (Shorts / Reels / Telegram)',
        };
    }
}
