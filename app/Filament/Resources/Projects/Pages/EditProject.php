<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Enums\ProjectStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\InvitationManager;
use App\Services\Notifier;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Top-up kuota AI (+N, audit).
            Action::make('topup')
                ->label('Top-up Kuota AI')
                ->icon('heroicon-o-plus-circle')
                ->schema([
                    TextInput::make('amount')->label('Tambah kuota')->numeric()->minValue(1)->maxValue(10)->default(1)->required(),
                ])
                ->action(function (Project $record, array $data): void {
                    $record->topUpAiQuota((int) $data['amount']);
                    Notification::make()->title('Kuota AI ditambah')->success()->send();
                }),

            // Tukar status (transisi sah SAHAJA — transitionTo).
            Action::make('changeStatus')
                ->label('Tukar Status')
                ->icon('heroicon-o-arrow-path')
                ->schema([
                    Select::make('status')->label('Status baharu')->required()
                        ->options(fn (Project $record) => collect($record->status->allowedTransitions())
                            ->mapWithKeys(fn (ProjectStatus $s) => [$s->value => $s->label()])->all()),
                ])
                ->action(function (Project $record, array $data): void {
                    try {
                        $target = ProjectStatus::from($data['status']);
                        $record->transitionTo($target, 'admin');

                        // §13 — notifikasi PIC untuk kemas kini binaan.
                        if (in_array($target, [ProjectStatus::InBuild, ProjectStatus::InReview, ProjectStatus::Live], true)) {
                            app(Notifier::class)->buildUpdated($record, $target->label(), 'pautan borang anda (menu Status)');
                        }

                        Notification::make()->title('Status dikemas kini')->success()->send();
                    } catch (InvalidStatusTransitionException $e) {
                        Notification::make()->title('Transisi tidak sah')->danger()->send();
                    }
                }),

            // Buka wizard sebagai PIC (jana token baharu untuk bantuan telefon).
            Action::make('openAsPic')
                ->label('Buka sebagai PIC')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->visible(fn (Project $record) => $record->invitation !== null)
                ->action(function (Project $record): void {
                    $token = app(InvitationManager::class)->regenerate($record->invitation);
                    Notification::make()
                        ->title('Token baharu dijana')
                        ->body(url('/b/'.$token))
                        ->persistent()->success()->send();
                }),
        ];
    }
}
