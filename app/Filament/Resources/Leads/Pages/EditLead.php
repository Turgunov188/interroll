<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Actions\Orders\CreateOrderFromLead;
use App\Exceptions\LeadAlreadyConverted;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Lead;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toOrder')
                ->label('Создать заказ')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->visible(fn (Lead $record): bool => $record->order === null)
                ->requiresConfirmation()
                ->modalDescription('Будет открыт новый заказ с данными клиента, статус заявки станет «Заказ».')
                ->action(function (Lead $record, CreateOrderFromLead $createOrder): void {
                    try {
                        $order = $createOrder->handle($record, auth()->user());
                    } catch (LeadAlreadyConverted $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();

                        return;
                    }

                    Notification::make()
                        ->title("Заказ {$order->code} создан")
                        ->body("Код и PIN для клиента: {$order->code} / {$order->pin}")
                        ->success()
                        ->persistent()
                        ->send();

                    $this->redirect(OrderResource::getUrl('edit', ['record' => $order]));
                }),
            Action::make('openOrder')
                ->label('Открыть заказ')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->visible(fn (Lead $record): bool => $record->order !== null)
                ->url(fn (Lead $record): string => OrderResource::getUrl('edit', ['record' => $record->order])),
            DeleteAction::make(),
        ];
    }
}
