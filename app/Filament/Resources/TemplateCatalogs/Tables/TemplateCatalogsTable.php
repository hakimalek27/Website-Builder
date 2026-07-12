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
                // URL dibina dari host permintaan sebenar (bukan APP_URL disk 'public') —
                // kukuh pada mana-mana port/host (dev serve port lain & prod). Selaras fix wizard `ec31cc7`.
                ImageColumn::make('thumbnail_path')->label('Thumbnail')
                    ->getStateUsing(fn ($record) => filled($record->thumbnail_path)
                        ? request()->getSchemeAndHttpHost().'/storage/'.$record->thumbnail_path
                        : null)
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
