<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Enums\OrderStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Attributes\On;

/** Status history shown to the customer on the tracking page (when public). */
class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $title = 'История этапов';

    protected static ?string $modelLabel = 'запись';

    #[On('refreshRelationManager')]
    public function refreshEvents(): void
    {
        $this->resetTable();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('status')
                    ->label('Этап')
                    ->options(OrderStatus::class)
                    ->default(fn () => $this->getOwnerRecord()->status)
                    ->required(),
                Toggle::make('is_public')
                    ->label('Видно клиенту')
                    ->default(true)
                    ->inline(false),
                TextInput::make('comment')
                    ->label('Комментарий')
                    ->placeholder('Например: литьё завершено, остывает')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Время')->dateTime('d.m.Y H:i'),
                TextColumn::make('status')->label('Этап')->badge(),
                TextColumn::make('comment')->label('Комментарий')->wrap()->placeholder('—'),
                IconColumn::make('is_public')->label('Клиенту')->boolean(),
                TextColumn::make('user.name')->label('Кто')->placeholder('система'),
            ])
            ->headerActions([
                CreateAction::make()->label('Добавить комментарий'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
