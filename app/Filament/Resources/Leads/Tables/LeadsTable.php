<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\LeadStatus;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Services\LeadQualifier;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mosque_name')->label('Masjid')->searchable()->sortable()->weight('bold'),
                TextColumn::make('state')->label('Negeri')->sortable(),
                TextColumn::make('pic_name')->label('PIC')->searchable(),
                TextColumn::make('pic_phone')->label('Telefon'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (LeadStatus $state) => $state->label())
                    ->color(fn (LeadStatus $state) => match ($state) {
                        LeadStatus::New => 'info',
                        LeadStatus::Contacted => 'warning',
                        LeadStatus::Qualified => 'success',
                        LeadStatus::Rejected => 'danger',
                    }),
                TextColumn::make('created_at')->label('Tarikh')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'Baharu',
                        'contacted' => 'Dihubungi',
                        'qualified' => 'Layak',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->recordActions([
                // Layakkan & Jemput (§5.3) — cipta Project + Invitation + hantar jemputan.
                Action::make('qualify')
                    ->label('Layakkan & Jemput')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Lead $record) => in_array($record->status, [LeadStatus::New, LeadStatus::Contacted], true))
                    ->schema([
                        TextInput::make('pic_email')
                            ->label('E-mel PIC')
                            ->email()
                            ->required()
                            ->default(fn (Lead $record) => $record->pic_email),
                        TextInput::make('token_days')
                            ->label('Tempoh token (hari)')
                            ->numeric()
                            ->minValue(1)
                            ->default(30)
                            ->required(),
                        TextInput::make('ai_quota')
                            ->label('Kuota AI')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->default(3)
                            ->required(),
                    ])
                    ->action(function (Lead $record, array $data): void {
                        app(LeadQualifier::class)->qualify(
                            $record,
                            $data['pic_email'],
                            (int) $data['token_days'],
                            (int) $data['ai_quota'],
                        );

                        Notification::make()
                            ->title('Lead dilayakkan & jemputan dihantar')
                            ->success()
                            ->send();
                    }),

                // Tanda dihubungi.
                Action::make('markContacted')
                    ->label('Tanda Dihubungi')
                    ->icon('heroicon-o-phone')
                    ->color('gray')
                    ->visible(fn (Lead $record) => $record->status === LeadStatus::New)
                    ->requiresConfirmation()
                    ->action(function (Lead $record): void {
                        $record->update(['status' => LeadStatus::Contacted]);
                        AuditLog::record('admin', null, 'lead.contacted', $record);
                    }),

                // Tolak (+sebab).
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Lead $record) => ! in_array($record->status, [LeadStatus::Qualified, LeadStatus::Rejected], true))
                    ->schema([
                        Textarea::make('reason')->label('Sebab tolak')->required()->maxLength(500),
                    ])
                    ->action(function (Lead $record, array $data): void {
                        $record->update([
                            'status' => LeadStatus::Rejected,
                            'rejected_reason' => $data['reason'],
                        ]);
                        AuditLog::record('admin', null, 'lead.rejected', $record, ['reason' => $data['reason']]);

                        Notification::make()->title('Lead ditolak')->warning()->send();
                    }),
            ]);
    }
}
