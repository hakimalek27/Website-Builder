<?php

namespace App\Filament\Resources\TemplateCatalogs\Pages;

use App\Filament\Resources\TemplateCatalogs\TemplateCatalogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTemplateCatalogs extends ListRecords
{
    protected static string $resource = TemplateCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
