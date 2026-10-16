<?php

namespace App\Filament\Resources\Certificates;

use App\Enums\MediaType;
use App\Filament\Resources\Certificates\Pages\ManageCertificates;
use App\Filament\Support\Localized;
use App\Models\Certificate;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Certificates and licences in «Нам доверяют» (factory page). */
class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'сертификат';

    protected static ?string $pluralModelLabel = 'Сертификаты';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                ...Localized::inputs('title', 'Название', required: true),
                TextInput::make('number')->label('Номер')->maxLength(255),
                DatePicker::make('valid_until')->label('Действует до')->native(false),
                FileUpload::make('file')->label('Скан (PDF или фото)')->disk(MediaType::DISK)->directory('certificates')->visibility('public')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])->maxSize(10240),
                Toggle::make('is_published')->label('Показывать')->default(true),
                TextInput::make('sort')->label('Порядок')->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('title_display')->label('Сертификат')->state(fn (Certificate $record) => $record->tr('title'))->wrap(),
                TextColumn::make('number')->label('Номер')->placeholder('—'),
                TextColumn::make('valid_until')->label('Действует до')->date('d.m.Y')->placeholder('—'),
                ToggleColumn::make('is_published')->label('На сайте'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCertificates::route('/')];
    }
}
