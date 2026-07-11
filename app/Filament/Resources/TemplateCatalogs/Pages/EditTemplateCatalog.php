<?php

namespace App\Filament\Resources\TemplateCatalogs\Pages;

use App\Filament\Resources\TemplateCatalogs\TemplateCatalogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTemplateCatalog extends EditRecord
{
    protected static string $resource = TemplateCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
