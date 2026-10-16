<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Last 10 leads on the dashboard; click a row to open it. */
class LatestLeads extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Последние заявки')
            ->query(fn () => LeadResource::getEloquentQuery()->latest()->limit(10))
            ->paginated(false)
            ->poll('30s')
            ->recordUrl(fn (Lead $record) => LeadResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('number')->label('№'),
                TextColumn::make('created_at')->label('Время')->since()->tooltip(fn (Lead $record) => $record->created_at?->format('d.m.Y H:i')),
                TextColumn::make('name')->label('Клиент')->description(fn (Lead $record) => $record->company),
                TextColumn::make('phone')->label('Телефон'),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')->label('Статус')->badge(),
                TextColumn::make('manager.name')->label('Менеджер')->placeholder('—'),
            ]);
    }
}
