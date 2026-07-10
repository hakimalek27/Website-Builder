<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\BriefBuilder;
use App\Services\Notifier;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Js;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

            // Muat turun brief MD penuh (Fasa 12 W3).
            Action::make('brief')
                ->label('Muat Turun Brief (MD)')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->action(fn (Project $record): StreamedResponse => response()->streamDownload(
                    fn () => print (app(BriefBuilder::class)->markdown($record)),
                    app(BriefBuilder::class)->fileName($record),
                    ['Content-Type' => 'text/markdown; charset=UTF-8'],
                )),

            // Lihat draf terkini dalam panel.
            Action::make('lihatDraf')
                ->label('Lihat Draf')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->visible(fn (Project $record) => $record->latestDraft !== null)
                ->url(fn (Project $record) => $record->latestDraft ? route('admin.draf', $record->latestDraft) : null, shouldOpenInNewTab: true),

            // Salin prompt jurutera terkini ke papan klip (§Fasa 14) — terus tampal ke Claude Code.
            Action::make('salinPrompt')
                ->label('Salin Prompt')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->visible(fn (Project $record): bool => self::latestEngineeredPrompt($record) !== null)
                ->action(function (Project $record, $livewire): void {
                    $prompt = self::latestEngineeredPrompt($record);
                    if ($prompt === null) {
                        return;
                    }
                    // Clipboard API perlu konteks selamat (localhost/HTTPS); guard elak ralat senyap.
                    $livewire->js('(navigator.clipboard ? navigator.clipboard.writeText('.Js::from($prompt).') : Promise.reject())');
                    Notification::make()->title('Prompt disalin — tampal ke Claude Code')->success()->send();
                }),
        ];
    }

    /** Prompt jurutera daripada penjanaan HTML terkini (corak sama BriefBuilder). */
    private static function latestEngineeredPrompt(Project $record): ?string
    {
        return $record->generations()->latest()->get()
            ->map(fn ($g) => $g->input_snapshot['engineered_prompt'] ?? null)
            ->filter()
            ->first();
    }
}
