<?php

namespace App\Filament\Resources\AiProviders\Tables;

use App\Models\AiProvider;
use App\Services\Ai\AiClientFactory;
use App\Services\Ai\AiException;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AiProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('bold'),
                TextColumn::make('driver')->label('Driver')->badge(),
                TextColumn::make('model')->label('Model'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                IconColumn::make('is_default')->label('Default')->boolean(),
            ])
            ->recordActions([
                // Uji Sambungan — panggilan mini sebenar (§5.3).
                Action::make('testConnection')
                    ->label('Uji Sambungan')
                    ->icon('heroicon-o-signal')
                    ->action(function (AiProvider $record): void {
                        try {
                            $client = app(AiClientFactory::class)->for($record);
                            $result = $client->complete('Balas dengan perkataan: OK', 'Balas: OK', $record);
                            Notification::make()
                                ->title('Sambungan berjaya')
                                ->body('Respons: '.Str::limit($result->content, 80))
                                ->success()->send();
                        } catch (AiException $e) {
                            Notification::make()
                                ->title('Sambungan gagal')
                                ->body(Str::limit($e->getMessage(), 120))
                                ->danger()->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
