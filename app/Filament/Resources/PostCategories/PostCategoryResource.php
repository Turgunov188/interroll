<?php

namespace App\Filament\Resources\PostCategories;

use App\Filament\Resources\PostCategories\Pages\ManagePostCategories;
use App\Filament\Support\Localized;
use App\Models\PostCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Blog rubrics: the filter buttons on /blog. */
class PostCategoryResource extends Resource
{
    protected static ?string $model = PostCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'рубрика';

    protected static ?string $pluralModelLabel = 'Рубрики блога';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                ...Localized::inputs('name', 'Название', required: true),
                TextInput::make('slug')->label('URL (slug)')->required()->alphaDash()->unique(ignoreRecord: true),
                TextInput::make('sort')->label('Порядок')->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name_display')->label('Название')->state(fn (PostCategory $record) => $record->tr('name')),
                TextColumn::make('slug')->label('Slug')->color('gray'),
                TextColumn::make('posts_count')->label('Статьи')->counts('posts'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePostCategories::route('/')];
    }
}
