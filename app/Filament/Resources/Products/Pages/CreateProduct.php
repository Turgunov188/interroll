<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\Catalog\CatalogCache;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /** See EditProduct::afterSave(): industries are synced after the product row is written. */
    protected function afterCreate(): void
    {
        app(CatalogCache::class)->flush();
    }
}
