<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    // Projek dicipta melalui Lead "Layakkan & Jemput" sahaja (ProjectResource::canCreate = false).
    // Tiada butang "Cipta" di senarai.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
