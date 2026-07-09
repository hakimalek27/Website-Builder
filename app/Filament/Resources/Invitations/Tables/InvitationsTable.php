<?php

namespace App\Filament\Resources\Invitations\Tables;

use App\Models\Invitation;
use App\Services\InvitationManager;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvitationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.mosque_name')->label('Masjid')->searchable()->sortable(),
                TextColumn::make('pic_name')->label('PIC')->searchable(),
                TextColumn::make('pic_email')->label('E-mel')->toggleable(),
                TextColumn::make('expires_at')->label('Luput')->dateTime('d/m/Y')->sortable()
                    ->color(fn (Invitation $r) => $r->isExpired() ? 'danger' : 'success'),
                IconColumn::make('opened_at')->label('Dibuka?')->boolean(),
                TextColumn::make('last_active_at')->label('Aktif terakhir')->since()->placeholder('—'),
                TextColumn::make('revoked_at')->label('Dibatalkan')->dateTime('d/m/Y')->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                // Hantar semula (jana token baharu + notifikasi).
                Action::make('resend')
                    ->label('Hantar semula')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Invitation $r) => ! $r->isRevoked())
                    ->requiresConfirmation()
                    ->modalDescription('Token BAHARU akan dijana (pautan lama tidak lagi sah) dan dihantar ke e-mel PIC.')
                    ->action(function (Invitation $record): void {
                        app(InvitationManager::class)->resend($record);
                        Notification::make()->title('Jemputan dihantar semula dengan pautan baharu')->success()->send();
                    }),

                // Salin pautan (jana token baharu & papar URL).
                Action::make('copyLink')
                    ->label('Jana & Salin Pautan')
                    ->icon('heroicon-o-link')
                    ->visible(fn (Invitation $r) => ! $r->isRevoked())
                    ->modalDescription('Token plaintext tidak disimpan, jadi tindakan ini menjana token BAHARU (menggantikan yang lama).')
                    ->action(function (Invitation $record): void {
                        $manager = app(InvitationManager::class);
                        $token = $manager->regenerate($record);
                        Notification::make()
                            ->title('Pautan baharu dijana')
                            ->body($manager->urlFor($token))
                            ->persistent()
                            ->success()
                            ->send();
                    }),

                // Lanjut tempoh (+N hari).
                Action::make('extend')
                    ->label('Lanjut tempoh')
                    ->icon('heroicon-o-calendar-days')
                    ->visible(fn (Invitation $r) => ! $r->isRevoked())
                    ->schema([
                        TextInput::make('days')->label('Tambah hari')->numeric()->minValue(1)->default(30)->required(),
                    ])
                    ->action(function (Invitation $record, array $data): void {
                        app(InvitationManager::class)->extend($record, (int) $data['days']);
                        Notification::make()->title('Tempoh dilanjutkan')->success()->send();
                    }),

                // Batalkan (revoke serta-merta).
                Action::make('revoke')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Invitation $r) => ! $r->isRevoked())
                    ->requiresConfirmation()
                    ->action(function (Invitation $record): void {
                        app(InvitationManager::class)->revoke($record);
                        Notification::make()->title('Jemputan dibatalkan')->warning()->send();
                    }),
            ]);
    }
}
