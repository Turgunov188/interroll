<?php

namespace App\Models\Concerns;

use App\Enums\Locale;

/**
 * Translatable JSON columns: {"ru": "...", "uz": "...", "en": "...", "tr": "..."}.
 * Usage: $product->tr('name')  → value in the current locale, falling back to ru (the main language).
 * In Filament: Localized::inputs('name', 'Название').
 */
trait HasLocalizedFields
{
    public function tr(string $field, Locale|string|null $locale = null): ?string
    {
        $value = $this->getAttribute($field);

        if (! is_array($value)) {
            return $value;
        }

        $locale = $locale instanceof Locale ? $locale->value : ($locale ?? app()->getLocale());
        $main = Locale::main()->value;

        foreach ([$locale, $main] as $key) {
            if (filled($value[$key] ?? null)) {
                return $value[$key];
            }
        }

        return collect($value)->first(fn ($v) => filled($v));
    }
}
