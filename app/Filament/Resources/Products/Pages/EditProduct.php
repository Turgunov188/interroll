<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\Catalog\CatalogCache;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Industries are a pivot relation: syncing them fires no model events and happens
     * after the product is saved, so the catalog cache is refreshed once more here.
     */
    protected function afterSave(): void
    {
        app(CatalogCache::class)->flush();
    }
}
