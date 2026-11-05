<?php

namespace App\Filament\Resources\Products\Pages;

use App\Actions\Products\ExportProducts;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // exactly what the table shows now: filters, search and sort included
            Action::make('excel')
                ->label('Скачать Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (ExportProducts $export) => $export->download($this->getFilteredSortedTableQuery())),
            CreateAction::make(),
        ];
    }
}
