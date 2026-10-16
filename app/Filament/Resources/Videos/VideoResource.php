<?php

namespace App\Filament\Resources\Videos;

use App\Enums\MediaType;
use App\Enums\VideoKind;
use App\Filament\Resources\Videos\Pages\CreateVideo;
use App\Filament\Resources\Videos\Pages\EditVideo;
use App\Filament\Resources\Videos\Pages\ListVideos;
use App\Filament\Support\Localized;
use App\Models\Video;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/** /video: films (YouTube or .mp4) for the player, and short clips. */
class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play-circle';

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'видео';

    protected static ?string $pluralModelLabel = 'Видео';

    public static function form(Schema $schema): Schema
    {
        $isMain = fn (Get $get): bool => ($get('kind') instanceof VideoKind ? $get('kind') : VideoKind::tryFrom((string) $get('kind'))) !== VideoKind::Short;

        return $schema->components([
            Section::make('Видео')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('kind')->label('Где показывать')->options(VideoKind::class)->default(VideoKind::Main)->required()->live(),
                    TextInput::make('duration')->label('Длительность')->placeholder('3:20')->regex('/^\d{1,2}(:\d{1,2}){1,2}$/')->maxLength(16),
                    ...Localized::inputs('title', 'Название', required: true),
                    ...Localized::inputs('category', 'Рубрика', configure: fn (TextInput $input) => $input->placeholder('Завод / Процесс / Качество')),
                    TextInput::make('url')
                        ->label('Ссылка на видео')
                        ->helperText('YouTube (обычное видео или Shorts). Для коротких можно Instagram или Telegram.')
                        ->url()
                        ->maxLength(500)
                        ->columnSpanFull(),
                    FileUpload::make('file')
                        ->label('Или загрузить файл .mp4')
                        ->helperText('Лучше через YouTube: файл большой и грузит сервер. Если загружен файл — показывается он.')
                        ->disk(MediaType::DISK)
                        ->directory('videos')
                        ->visibility('public')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                        ->maxSize(204800)
                        ->visible($isMain),
                    FileUpload::make('poster')
                        ->label('Обложка')
                        ->helperText('Для YouTube можно не загружать — возьмётся обложка с YouTube.')
                        ->image()
                        ->disk(MediaType::DISK)
                        ->directory('videos')
                        ->visibility('public')
                        ->maxSize(4096),
                    Toggle::make('is_published')->label('Показывать на сайте'),
                    TextInput::make('sort')->label('Порядок')->numeric()->default(0),
                ]),
            Section::make('Описание и главы')
                ->columnSpanFull()
                ->visible($isMain)
                ->schema([
                    Localized::tabs('description', 'Описание'),
                    Repeater::make('chapters')
                        ->label('Главы (метки на шкале, клик — переход)')
                        ->defaultItems(0)
                        ->columns(5)
                        ->addActionLabel('Добавить главу')
                        ->schema([
                            TextInput::make('at')->label('Время')->placeholder('0:45')->required()->regex('/^\d{1,2}(:\d{1,2}){1,2}$/'),
                            ...Localized::inputs('label', 'Глава'),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('poster')->label('')->disk(MediaType::DISK),
                TextColumn::make('title_display')->label('Видео')->state(fn (Video $record) => $record->tr('title'))->wrap(),
                TextColumn::make('kind')->label('Где')->badge(),
                TextColumn::make('duration')->label('Длит.')->placeholder('—'),
                ToggleColumn::make('is_published')->label('На сайте'),
            ])
            ->filters([SelectFilter::make('kind')->label('Где')->options(VideoKind::class)])
            ->recordActions([EditAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVideos::route('/'),
            'create' => CreateVideo::route('/create'),
            'edit' => EditVideo::route('/{record}/edit'),
        ];
    }
}
