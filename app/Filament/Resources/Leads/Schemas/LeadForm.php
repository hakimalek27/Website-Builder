<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mosque_name')->label('Nama masjid')->required()->maxLength(150),
                Select::make('state')->label('Negeri')->options(array_combine(config('reka.states'), config('reka.states')))->required(),
                TextInput::make('pic_name')->label('Nama PIC')->required()->maxLength(100),
                TextInput::make('pic_phone')->label('Telefon')->required(),
                TextInput::make('pic_email')->label('E-mel')->email()->maxLength(150),
                TextInput::make('current_website')->label('Laman sedia ada')->url()->maxLength(200),
                Textarea::make('notes')->label('Catatan')->maxLength(500)->columnSpanFull(),
                Select::make('status')->label('Status')->options([
                    'new' => 'Baharu',
                    'contacted' => 'Dihubungi',
                    'qualified' => 'Layak',
                    'rejected' => 'Ditolak',
                ])->required(),
                Textarea::make('rejected_reason')->label('Sebab tolak')->maxLength(500)->columnSpanFull(),
            ]);
    }
}
