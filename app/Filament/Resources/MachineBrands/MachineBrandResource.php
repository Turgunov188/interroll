<?php

namespace App\Filament\Resources\MachineBrands;

use App\Filament\Resources\MachineBrands\Pages\ManageMachineBrands;
use App\Models\MachineBrand;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/** Станки: Monforts, Bruckner, Artos… — the catalog «Станок» filter (simple list edited in a modal). */
class MachineBrandResource extends Resource
{
    protected static ?string $model = MachineBrand::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'станок';

    protected static ?string $pluralModelLabel = 'Станки (бренды)';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components(self::fields());
    }

    /** Also used by the «create» button next to the product's machine select. */
    public static function fields(): array
    {
        return [
            TextInput::make('name')
                ->label('Название')
                ->placeholder('Monforts')
                ->required()
                ->maxLength(80)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                    if ($operation === 'create') {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            TextInput::make('slug')->label('URL (slug)')->required()->unique(MachineBrand::class, 'slug', ignoreRecord: true),
            TextInput::make('sort')->label('Порядок')->numeric()->default(0),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name')->label('Название')->searchable(),
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
            'index' => ManageMachineBrands::route('/'),
        ];
    }
}
