<?php

namespace App\Filament\Support;

use App\Enums\Locale;
use Closure;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * Form helpers for translatable JSON columns: {"ru": "...", "uz": "...", "en": "...", "tr": "..."}.
 * The main language (ru) is required, the others fall back to it on the site.
 *
 * Usage: ...Localized::inputs('name', 'Название', required: true)
 */
final class Localized
{
    /**
     * One text input per language: Название (RU) | Название (UZ) | …
     *
     * @param  (Closure(TextInput, Locale): TextInput)|null  $configure
     * @return list<TextInput>
     */
    public static function inputs(string $field, string $label, bool $required = false, ?Closure $configure = null): array
    {
        return array_map(function (Locale $locale) use ($field, $label, $required, $configure): TextInput {
            $input = TextInput::make("{$field}.{$locale->value}")
                ->label($label.' ('.strtoupper($locale->value).')')
                ->maxLength(255)
                ->required($required && $locale->isMain());

            return $configure ? $configure($input, $locale) : $input;
        }, Locale::cases());
    }

    /** Long text: one tab per language. */
    public static function tabs(string $field, string $label, bool $rich = false): Tabs
    {
        return Tabs::make($label)
            ->tabs(array_map(fn (Locale $locale): Tab => Tab::make($locale->getLabel())->schema([
                $rich
                    ? RichEditor::make("{$field}.{$locale->value}")->label($label)->toolbarButtons([
                        'bold', 'italic', 'bulletList', 'orderedList', 'h2', 'h3', 'link', 'undo', 'redo',
                    ])
                    : Textarea::make("{$field}.{$locale->value}")->label($label)->rows(3),
            ]), Locale::cases()))
            ->columnSpanFull();
    }
}
