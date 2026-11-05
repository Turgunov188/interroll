<?php

namespace App\Filament\Resources\MachineBrands\Pages;

use App\Filament\Resources\MachineBrands\MachineBrandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMachineBrands extends ManageRecords
{
    protected static string $resource = MachineBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
