<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Enums\Locale;
use App\Filament\Support\Localized;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'раздел';

    protected static ?string $pluralModelLabel = 'Разделы';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основное')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    ...Localized::inputs('name', 'Название', required: true, configure: fn (TextInput $input, Locale $locale) => $locale->isMain()
                        ? $input->live(onBlur: true)->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug((string) $state));
                            }
                        })
                        : $input),
                    TextInput::make('slug')
                        ->label('URL (slug)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Select::make('parent_id')
                        ->label('Родительский раздел')
                        ->relationship('parent', 'slug', fn ($query, ?Category $record) => $record ? $query->whereKeyNot($record->getKey()) : $query)
                        ->getOptionLabelFromRecordUsing(fn (Category $record) => $record->tr('name'))
                        ->searchable()
                        ->preload()
                        ->placeholder('— корневой раздел —'),
                    Localized::tabs('description', 'Описание'),
                    FileUpload::make('image')
                        ->label('Изображение')
                        ->image()
                        ->disk('public')
                        ->directory('categories')
                        ->maxSize(4096),
                    TextInput::make('sort')->label('Порядок')->numeric()->default(0),
                    Toggle::make('is_active')->label('Показывать на сайте')->default(true),
                ]),
            Section::make('SEO')
                ->collapsed()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    ...Localized::inputs('seo.title', 'Заголовок (title)'),
                    ...Localized::inputs('seo.description', 'Описание (description)'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->square(),
                TextColumn::make('name_display')
                    ->label('Название')
                    ->state(fn (Category $record) => $record->tr('name'))
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('name->uz', 'like', "%{$search}%")
                        ->orWhere('name->ru', 'like', "%{$search}%")),
                TextColumn::make('parent_display')
                    ->label('Родительский раздел')
                    ->state(fn (Category $record) => $record->parent?->tr('name'))
                    ->placeholder('—'),
                TextColumn::make('products_count')->label('Товары')->counts('products'),
                ToggleColumn::make('is_active')->label('Активен'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
