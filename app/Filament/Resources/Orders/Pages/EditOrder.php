<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openLead')
                ->label('Открыть заявку')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('gray')
                ->visible(fn (Order $record) => $record->lead_id !== null)
                ->url(fn (Order $record) => LeadResource::getUrl('edit', ['record' => $record->lead_id])),
            DeleteAction::make(),
        ];
    }

    /** Status changes add a history row (model event); refresh the list below the form. */
    protected function afterSave(): void
    {
        $this->dispatch('refreshRelationManager');
    }
}
