<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Enums\Locale;
use App\Filament\Resources\Products\ProductResource;
use App\Models\LeadItem;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

/** Parts the customer asked for (request cart / configurator). */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Детали';

    protected static ?string $modelLabel = 'деталь';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('product_id')
                    ->label('Товар из каталога')
                    ->options(fn (): array => self::catalogOptions())
                    ->searchable()
                    ->placeholder('Выберите товар или введите артикул / название')
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state): void {
                        if ($state && $product = Product::find($state)) {
                            $set('product_name', $product->tr('name', Locale::Ru));
                        }
                    })
                    ->columnSpanFull(),
                TextInput::make('product_name')->label('Название')->required()->maxLength(255),
                TextInput::make('quantity')->label('Количество')->numeric()->minValue(1)->default(1)->required(),
                KeyValue::make('options')
                    ->label('Параметры')
                    ->keyLabel('Параметр')
                    ->valueLabel('Значение')
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Active catalog grouped by section: ['Валы' => [id => 'IR-V01 — Полиуретановый вал'], …].
     * The catalog is small (tens of items), so it is loaded at once and searched in the browser
     * by SKU and name; switch to getSearchResultsUsing() if it grows to thousands.
     *
     * @return array<string, array<int, string>>
     */
    private static function catalogOptions(): array
    {
        return Product::query()
            ->active()
            ->with('category')
            ->ordered()
            ->get()
            ->groupBy(fn (Product $product): string => (string) ($product->category?->tr('name', Locale::Ru) ?? 'Без раздела'))
            ->map(fn (Collection $group): array => $group
                ->mapWithKeys(fn (Product $product): array => [$product->getKey() => $product->sku.' — '.$product->tr('name', Locale::Ru)])
                ->all())
            ->all();
    }

    /** Parts sent from the site are the customer's request: listed read-only; the manager may add more. */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Деталь')
                    ->weight(FontWeight::Bold)
                    ->description(fn (LeadItem $record): ?string => $record->product?->sku)
                    ->url(fn (LeadItem $record): ?string => $record->product ? ProductResource::getUrl('edit', ['record' => $record->product]) : null),
                TextColumn::make('quantity')->label('Кол-во')->suffix(' шт.'),
                TextColumn::make('options')
                    ->label('Выбранные параметры')
                    ->state(fn (LeadItem $record): array => collect($record->options ?? [])
                        ->map(fn ($value, $key): string => is_scalar($value) ? "{$key}: {$value}" : (string) $key)
                        ->values()
                        ->all())
                    ->listWithLineBreaks()
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()->label('Добавить деталь'),
            ]);
    }
}
