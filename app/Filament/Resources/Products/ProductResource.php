<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\MediaRelationManager;
use App\Enums\Configurator;
use App\Enums\Locale;
use App\Enums\Material;
use App\Filament\Support\Localized;
use App\Models\Category;
use App\Models\Industry;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'товар';

    protected static ?string $pluralModelLabel = 'Товары';

    protected static ?string $recordTitleAttribute = 'sku';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Товар')
                ->columnSpanFull()
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Основное')
                        ->icon('heroicon-o-information-circle')
                        ->columns(2)
                        ->schema([
                            ...Localized::inputs('name', 'Название', required: true, configure: fn (TextInput $input, Locale $locale) => $locale->isMain()
                                ? $input->live(onBlur: true)->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                })
                                : $input),
                            TextInput::make('sku')
                                ->label('Артикул')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(64),
                            TextInput::make('slug')
                                ->label('URL (slug)')
                                ->required()
                                ->unique(ignoreRecord: true),
                            Select::make('category_id')
                                ->label('Раздел')
                                ->relationship('category', 'slug')
                                ->getOptionLabelFromRecordUsing(fn (Category $record) => $record->tr('name'))
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('industries')
                                ->label('Отрасли')
                                ->relationship('industries', 'slug')
                                ->getOptionLabelFromRecordUsing(fn (Industry $record) => $record->tr('name'))
                                ->multiple()
                                ->preload(),
                            ...Localized::inputs('short', 'Краткое описание (для карточки)'),
                            Localized::tabs('description', 'Полное описание', rich: true),
                        ]),

                    Tab::make('Технические')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->columns(4)
                        ->schema([
                            Select::make('material')
                                ->label('Материал')
                                ->options(Material::class)
                                ->default(Material::Polyurethane)
                                ->required(),
                            TextInput::make('hardness')
                                ->label('Твёрдость (от)')
                                ->numeric()
                                ->minValue(10)
                                ->maxValue(100)
                                ->placeholder('90'),
                            TextInput::make('hardness_max')
                                ->label('Твёрдость (до)')
                                ->helperText('Для диапазона, например 70–95')
                                ->numeric()
                                ->minValue(10)
                                ->maxValue(100)
                                ->gte('hardness'),
                            Select::make('hardness_scale')
                                ->label('Шкала')
                                ->options(['A' => 'Shore A', 'D' => 'Shore D'])
                                ->default('A'),
                            Repeater::make('specs')
                                ->label('Характеристики')
                                ->columnSpanFull()
                                ->columns(['default' => 2, 'lg' => 6])
                                ->reorderable()
                                ->collapsible()
                                ->defaultItems(0)
                                ->addActionLabel('Добавить характеристику')
                                ->itemLabel(fn (array $state): ?string => trim(($state['name']['ru'] ?? $state['name']['uz'] ?? '').' '.($state['value'] ?? '').' '.($state['unit'] ?? '')) ?: null)
                                ->schema([
                                    TextInput::make('name.ru')->label('Название (RU)')->placeholder('Диаметр')->required(),
                                    TextInput::make('name.uz')->label('UZ')->placeholder('Diametr'),
                                    TextInput::make('name.en')->label('EN')->placeholder('Diameter'),
                                    TextInput::make('name.tr')->label('TR')->placeholder('Çap'),
                                    TextInput::make('value')->label('Значение')->placeholder('120')->required(),
                                    TextInput::make('unit')->label('Ед. изм.')->placeholder('mm'),
                                ]),
                            Select::make('configurator')
                                ->label('Конфигуратор на сайте')
                                ->options(Configurator::class)
                                ->placeholder('Нет — только чертежи, 3D и фото')
                                ->helperText('Чертежи, 3D-модель, фото и документы загружаются ниже, во вкладке «Файлы».')
                                ->columnSpan(2),
                            Toggle::make('made_to_order')
                                ->label('Изготавливается по чертежу')
                                ->default(true)
                                ->inline(false)
                                ->columnSpan(2),
                        ]),

                    Tab::make('Цена и статус')
                        ->icon('heroicon-o-banknotes')
                        ->columns(2)
                        ->schema([
                            Toggle::make('price_on_request')
                                ->label('Цена по запросу')
                                ->default(true)
                                ->live(),
                            TextInput::make('price')
                                ->label('Цена')
                                ->numeric()
                                ->suffix('сум')
                                ->hidden(fn (Get $get): bool => (bool) $get('price_on_request')),
                            Toggle::make('is_featured')->label('Показывать на главной'),
                            Toggle::make('is_active')->label('Показывать на сайте')->default(true),
                            TextInput::make('sort')->label('Порядок')->numeric()->default(0),
                        ]),

                    Tab::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->columns(2)
                        ->schema([
                            ...Localized::inputs('seo.title', 'Заголовок (title)'),
                            ...Localized::inputs('seo.description', 'Описание (description)'),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                ImageColumn::make('main_image')
                    ->label('')
                    ->state(fn (Product $record) => $record->mainImage())
                    ->disk('public')
                    ->square(),
                TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily(FontFamily::Mono),
                TextColumn::make('name_display')
                    ->label('Название')
                    ->state(fn (Product $record) => $record->tr('name'))
                    ->wrap()
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->where('name->ru', 'like', "%{$search}%")
                        ->orWhere('name->uz', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")
                        ->orWhere('name->tr', 'like', "%{$search}%")),
                TextColumn::make('category_display')
                    ->label('Раздел')
                    ->state(fn (Product $record) => $record->category?->tr('name'))
                    ->toggleable(),
                TextColumn::make('material')
                    ->label('Материал')
                    ->badge(),
                TextColumn::make('hardness')
                    ->label('Shore')
                    ->formatStateUsing(fn (Product $record) => $record->hardnessLabel())
                    ->sortable()
                    ->placeholder('—'),
                IconColumn::make('is_featured')->label('На главной')->boolean()->toggleable(),
                ToggleColumn::make('is_active')->label('Активен'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Раздел')
                    ->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn (Category $record) => $record->tr('name'))
                    ->preload(),
                SelectFilter::make('material')->label('Материал')->options(Material::class),
                TernaryFilter::make('is_active')->label('Активен'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Копия')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['sku'] .= '-copy';
                        $data['slug'] .= '-'.Str::lower(Str::random(4));
                        $data['is_active'] = false;

                        return $data;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'mainPhoto']);
    }

    public static function getRelations(): array
    {
        return [
            MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
