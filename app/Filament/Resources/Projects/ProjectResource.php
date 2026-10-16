<?php

namespace App\Filament\Resources\Projects;

use App\Enums\Locale;
use App\Enums\MediaType;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Support\Localized;
use App\Models\Industry;
use App\Models\Project;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/** «Работы»: completed jobs on /projects (problem → solution → result). */
class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'работа';

    protected static ?string $pluralModelLabel = 'Работы';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Основное')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    ...Localized::inputs('title', 'Заголовок', required: true, configure: fn (TextInput $input, Locale $locale) => $locale->isMain()
                        ? $input->live(onBlur: true)->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug((string) $state));
                            }
                        })
                        : $input),
                    TextInput::make('slug')->label('URL (slug)')->required()->unique(ignoreRecord: true)->maxLength(255),
                    Select::make('industry_id')
                        ->label('Отрасль')
                        ->helperText('По ней работает фильтр на странице «Работы»')
                        ->relationship('industry', 'slug')
                        ->getOptionLabelFromRecordUsing(fn (Industry $record) => $record->tr('name'))
                        ->searchable()
                        ->preload(),
                    ...Localized::inputs('client', 'Заказчик', configure: fn (TextInput $input) => $input->placeholder('Например: металлургический комбинат')),
                    ...Localized::inputs('short', 'Кратко (для карточки)'),
                    Toggle::make('is_published')->label('Показывать на сайте'),
                    TextInput::make('sort')->label('Порядок')->numeric()->default(0),
                ]),
            Section::make('Задача → решение → результат')
                ->columnSpanFull()
                ->schema([
                    Localized::tabs('problem', 'Задача'),
                    Localized::tabs('solution', 'Решение'),
                    Localized::tabs('result', 'Результат'),
                ]),
            Section::make('Цифры')
                ->description('Первые две — на карточке, все три — в описании работы.')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('kpis')
                        ->hiddenLabel()
                        ->maxItems(3)
                        ->columns(5)
                        ->defaultItems(0)
                        ->addActionLabel('Добавить цифру')
                        ->schema([
                            TextInput::make('value')->label('Значение')->placeholder('3× / 40% / 12 дней')->required()->maxLength(32),
                            ...Localized::inputs('label', 'Подпись'),
                        ]),
                ]),
            Section::make('Фото')
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    self::photo('cover', 'Обложка (карточка)'),
                    self::photo('photo_before', 'До'),
                    self::photo('photo_after', 'После'),
                ]),
            Section::make('Отзыв заказчика')
                ->collapsed()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Localized::tabs('quote', 'Отзыв'),
                    TextInput::make('quote_author')->label('Автор отзыва')->placeholder('Имя, должность, предприятие')->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('cover')->label('')->disk(MediaType::DISK)->square(),
                TextColumn::make('title_display')->label('Работа')->state(fn (Project $record) => $record->tr('title'))->wrap(),
                TextColumn::make('industry_display')->label('Отрасль')->state(fn (Project $record) => $record->industry?->tr('name'))->placeholder('—'),
                ToggleColumn::make('is_published')->label('На сайте'),
                TextColumn::make('updated_at')->label('Изменено')->since()->color('gray'),
            ])
            ->filters([
                SelectFilter::make('industry_id')->label('Отрасль')->relationship('industry', 'slug')
                    ->getOptionLabelFromRecordUsing(fn (Industry $record) => $record->tr('name')),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    private static function photo(string $column, string $label): FileUpload
    {
        return FileUpload::make($column)
            ->label($label)
            ->image()
            ->imageEditor()
            ->disk(MediaType::DISK)
            ->directory('projects')
            ->visibility('public')
            ->maxSize(8192);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
