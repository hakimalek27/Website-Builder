<?php

namespace App\Filament\Resources\TemplateCatalogs;

use App\Filament\Resources\TemplateCatalogs\Pages\CreateTemplateCatalog;
use App\Filament\Resources\TemplateCatalogs\Pages\EditTemplateCatalog;
use App\Filament\Resources\TemplateCatalogs\Pages\ListTemplateCatalogs;
use App\Filament\Resources\TemplateCatalogs\Schemas\TemplateCatalogForm;
use App\Filament\Resources\TemplateCatalogs\Tables\TemplateCatalogsTable;
use App\Models\TemplateCatalog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TemplateCatalogResource extends Resource
{
    protected static ?string $model = TemplateCatalog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Katalog Templat';

    protected static ?string $modelLabel = 'Templat';

    protected static ?string $pluralModelLabel = 'Katalog Templat';

    protected static string|\UnitEnum|null $navigationGroup = 'Konfigurasi';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return TemplateCatalogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TemplateCatalogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTemplateCatalogs::route('/'),
            'create' => CreateTemplateCatalog::route('/create'),
            'edit' => EditTemplateCatalog::route('/{record}/edit'),
        ];
    }
}
