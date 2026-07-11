<?php

namespace App\Filament\Resources\TemplateCatalogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TemplateCatalogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_path')->label('Thumbnail')->disk('public')
                    ->height(48),
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('bold')->wrap(),
                TextColumn::make('source')->label('Sumber')->badge(),
                TextColumn::make('categories')->label('Kategori')->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'masjid' => 'Masjid', 'ngo' => 'NGO', default => $state,
                    }),
                TextColumn::make('style_tags')->label('Gaya')->badge()->toggleable(),
                ToggleColumn::make('is_active')->label('Aktif'),
                TextColumn::make('sort')->label('Susunan')->sortable(),
            ])
            ->defaultSort('sort')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
