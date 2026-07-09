<?php

namespace App\Filament\Resources\Invitations;

use App\Filament\Resources\Invitations\Pages\ListInvitations;
use App\Filament\Resources\Invitations\Tables\InvitationsTable;
use App\Models\Invitation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Jemputan';

    protected static ?string $modelLabel = 'Jemputan';

    protected static ?string $pluralModelLabel = 'Jemputan';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return InvitationsTable::configure($table);
    }

    // Jemputan dicipta melalui "Layakkan & Jemput" (LeadResource), bukan manual.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvitations::route('/'),
        ];
    }
}
