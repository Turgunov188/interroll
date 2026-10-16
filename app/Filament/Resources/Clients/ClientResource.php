<?php

namespace App\Filament\Resources\Clients;

use App\Enums\MediaType;
use App\Filament\Resources\Clients\Pages\ManageClients;
use App\Models\Client;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Client logos in «Нам доверяют» (factory page). */
class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|UnitEnum|null $navigationGroup = 'Контент';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'клиент';

    protected static ?string $pluralModelLabel = 'Клиенты (логотипы)';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->label('Компания')->required()->maxLength(255),
                FileUpload::make('logo')->label('Логотип')->image()->disk(MediaType::DISK)->directory('clients')->visibility('public')->maxSize(2048)
                    ->helperText('PNG или SVG на прозрачном фоне. Без логотипа показывается название.'),
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
                ImageColumn::make('logo')->label('')->disk(MediaType::DISK),
                TextColumn::make('name')->label('Компания')->searchable(),
                ToggleColumn::make('is_published')->label('На сайте'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageClients::route('/')];
    }
}
