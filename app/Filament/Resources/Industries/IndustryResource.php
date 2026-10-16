<?php

namespace App\Filament\Resources\Industries;

use App\Filament\Resources\Industries\Pages\ManageIndustries;
use App\Filament\Support\Localized;
use App\Models\Industry;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Sohalar: konchilik, qog'oz, metallurgiya... (simple list edited in a modal). */
class IndustryResource extends Resource
{
    protected static ?string $model = Industry::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'отрасль';

    protected static ?string $pluralModelLabel = 'Отрасли';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                ...Localized::inputs('name', 'Название', required: true),
                TextInput::make('slug')->label('URL (slug)')->required()->unique(ignoreRecord: true),
                TextInput::make('sort')->label('Порядок')->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name_display')->label('Название')->state(fn (Industry $record) => $record->tr('name')),
                TextColumn::make('slug')->label('Slug')->color('gray'),
                TextColumn::make('products_count')->label('Товары')->counts('products'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageIndustries::route('/'),
        ];
    }
}
