<?php

namespace App\Enums;

/** How a site setting is stored, edited and printed on the page. */
enum SettingKind: string
{
    case Text = 'text';             // one value for every language: a code, a name, a number with units
    case Number = 'number';
    case Phone = 'phone';           // shown as typed, the tel: link is built from its digits
    case Localized = 'localized';   // {"ru": …, "uz": …, "en": …, "tr": …}
    case Note = 'note';             // localized optional sentence; empty → nothing is printed
    case Image = 'image';           // file on the public disk

    /** Printed when the admin has not filled the value yet, so a gap is visible on the site. */
    public function fallback(): string
    {
        return match ($this) {
            self::Note, self::Image => '',
            default => '[__]',
        };
    }

    public function isLocalized(): bool
    {
        return $this === self::Localized || $this === self::Note;
    }
}
