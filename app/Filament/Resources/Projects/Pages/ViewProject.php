<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\Notifier;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Tanda nota PIC yang belum dibaca sebagai telah dibaca (§5.2 P11).
        $this->record->notes()->where('author', 'pic')->whereNull('read_at')->update(['read_at' => now()]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Tindakan (Top-up / Status / PIC)'),

            // Balas nota PIC + (pilihan) WhatsApp.
            Action::make('balasNota')
                ->label('Balas Nota PIC')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    Textarea::make('body')->label('Balasan')->required()->maxLength(2000)->rows(4),
                    Toggle::make('hantar_wa')->label('Hantar WhatsApp kepada PIC')->default(true),
                ])
                ->action(function (Project $record, array $data): void {
                    $note = $record->notes()->create([
                        'author' => 'admin',
                        'author_name' => auth()->user()?->name ?? 'Admin REKA',
                        'kind' => 'general',
                        'body' => $data['body'],
                    ]);

                    if ($data['hantar_wa'] ?? false) {
                        app(Notifier::class)->adminReplied($record, $note);
                    }

                    Notification::make()->title('Balasan dihantar kepada PIC')->success()->send();
                }),

            // Lihat draf terkini dalam panel.
            Action::make('lihatDraf')
                ->label('Lihat Draf')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn (Project $record) => $record->latestDraft !== null)
                ->url(fn (Project $record) => $record->latestDraft ? route('admin.draf', $record->latestDraft) : null, shouldOpenInNewTab: true),
        ];
    }
}
