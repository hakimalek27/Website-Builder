<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Services\BriefBuilder;
use App\Services\HandoverExporter;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mosque_name')->label('Masjid')->searchable()->sortable()->weight('bold'),
                TextColumn::make('tier')->label('Tier')->badge(),
                TextColumn::make('state')->label('Negeri'),
                // ProjectStatus implement HasLabel+HasColor → badge auto-label & auto-warna (elak container resolve enum).
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('quota_ai_used')->label('Kuota AI')
                    ->formatStateUsing(fn ($state, Project $r) => "{$state}/{$r->quota_ai_total}"),
                TextColumn::make('cost')->label('Kos AI (USD)')
                    ->state(fn (Project $r) => number_format((float) $r->generations()->sum('cost_estimate'), 2)),
                TextColumn::make('updated_at')->label('Aktif')->since(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                ViewAction::make(),

                // Fasa 12 W3 — Muat turun brief MD penuh (submitted+ sahaja).
                Action::make('brief')
                    ->label('Brief (MD)')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->visible(fn (Project $r) => ! in_array($r->status, [
                        ProjectStatus::Invited, ProjectStatus::InProgress, ProjectStatus::Cancelled, ProjectStatus::Expired,
                    ], true))
                    ->action(fn (Project $record): StreamedResponse => response()->streamDownload(
                        fn () => print (app(BriefBuilder::class)->markdown($record)),
                        app(BriefBuilder::class)->fileName($record),
                        ['Content-Type' => 'text/markdown; charset=UTF-8'],
                    )),

                // §14 — Eksport Pakej Serahan (approved+ sahaja).
                Action::make('exportHandover')
                    ->label('Eksport Pakej Serahan')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->visible(fn (Project $r) => in_array($r->status, [
                        ProjectStatus::Approved, ProjectStatus::HandoverExported, ProjectStatus::InBuild,
                        ProjectStatus::InReview, ProjectStatus::Live,
                    ], true))
                    ->action(function (Project $record): StreamedResponse {
                        $export = app(HandoverExporter::class)->export($record);

                        if ($record->status === ProjectStatus::Approved) {
                            $record->transitionTo(ProjectStatus::HandoverExported, 'admin');
                        }

                        Notification::make()->title('Pakej serahan dieksport')->success()->send();

                        return Storage::disk('local')->download($export->zip_path, basename($export->zip_path));
                    }),
            ]);
    }
}
