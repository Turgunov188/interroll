<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Actions\Products\AttachProductMedia;
use App\Enums\MediaType;
use App\Filament\Support\Localized;
use App\Models\Product;
use App\Models\ProductMedia;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** «Файлы» on the product page: photos, drawings, 3D model and documents, each in its own type. */
class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Файлы';

    protected static ?string $modelLabel = 'файл';

    protected static ?string $pluralModelLabel = 'файлы';

    public const MAX_PHOTOS_PER_UPLOAD = 20;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('type')
                    ->label('Тип')
                    ->options(MediaType::class)
                    ->default(MediaType::Photo)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('path', null))
                    ->columnSpanFull(),
                FileUpload::make('path')
                    ->label('Файл')
                    ->disk(MediaType::DISK)
                    ->visibility('public')
                    ->directory(fn (Get $get): string => self::typeOf($get)->directory())
                    ->acceptedFileTypes(fn (Get $get): array => self::typeOf($get)->acceptedMimeTypes())
                    ->maxSize(fn (Get $get): int => self::typeOf($get)->maxKilobytes())
                    ->helperText(fn (Get $get): string => self::typeOf($get)->hint())
                    ->storeFileNamesIn('original_name')
                    ->downloadable()
                    ->openable()
                    ->required()
                    ->columnSpanFull(),
                ...Localized::inputs('alt', 'Подпись (alt)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('preview')
                    ->label('')
                    ->state(fn (ProductMedia $record): ?string => $record->isImage() ? $record->path : null)
                    ->disk(MediaType::DISK)
                    ->square(),
                TextColumn::make('type')->label('Тип')->badge(),
                TextColumn::make('original_name')
                    ->label('Файл')
                    ->description(fn (ProductMedia $record): string => $record->sizeLabel())
                    ->wrap(),
                TextColumn::make('alt_display')
                    ->label('Подпись')
                    ->state(fn (ProductMedia $record): ?string => $record->tr('alt'))
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('Тип')->options(MediaType::class),
            ])
            ->headerActions([
                $this->uploadPhotosAction(),
                CreateAction::make()->label('Добавить файл')->icon('heroicon-o-plus'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Открыть')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (ProductMedia $record): string => $record->url(), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Several photos at once — the common case when a product is being filled in. */
    private function uploadPhotosAction(): Action
    {
        $photo = MediaType::Photo;

        return Action::make('uploadPhotos')
            ->label('Загрузить фото')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->modalSubmitActionLabel('Загрузить')
            ->schema([
                FileUpload::make('paths')
                    ->label('Фото')
                    ->helperText($photo->hint())
                    ->multiple()
                    ->image()
                    ->reorderable()
                    ->appendFiles()
                    ->panelLayout('grid')
                    ->disk(MediaType::DISK)
                    ->visibility('public')
                    ->directory($photo->directory())
                    ->acceptedFileTypes($photo->acceptedMimeTypes())
                    ->maxSize($photo->maxKilobytes())
                    ->maxFiles(self::MAX_PHOTOS_PER_UPLOAD)
                    ->storeFileNamesIn('names')
                    ->required(),
            ])
            ->action(function (array $data, AttachProductMedia $attach): void {
                /** @var Product $product */
                $product = $this->getOwnerRecord();
                $added = $attach->handle($product, MediaType::Photo, (array) $data['paths'], (array) ($data['names'] ?? []));

                Notification::make()->title('Загружено фото: '.$added->count())->success()->send();
            });
    }

    private static function typeOf(Get $get): MediaType
    {
        $type = $get('type');

        return $type instanceof MediaType ? $type : (MediaType::tryFrom((string) $type) ?? MediaType::Photo);
    }
}
