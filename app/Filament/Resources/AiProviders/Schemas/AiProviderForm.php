<?php

namespace App\Filament\Resources\AiProviders\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AiProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama')->required()->maxLength(80),
                Select::make('driver')->label('Driver')->required()
                    ->options(['anthropic' => 'Anthropic (Claude)', 'openai_compatible' => 'OpenAI-compatible'])
                    ->default('anthropic'),
                TextInput::make('base_url')->label('Base URL (pilihan)')
                    ->placeholder('cth: http://host:11434/v1 (Ollama) — kosong untuk lalai'),
                TextInput::make('api_key')
                    ->label('API Key')
                    ->password()
                    ->revealable()
                    // Write-only: kosong = kekal nilai lama; paparan ••••+4 melalui placeholder.
                    ->dehydrated(fn ($state) => filled($state))
                    ->placeholder(fn ($record) => $record?->api_key ? '••••'.substr($record->api_key, -4) : 'Masukkan kunci')
                    ->required(fn (string $operation) => $operation === 'create'),
                TextInput::make('model')->label('Model')->required()
                    ->placeholder('cth: claude-sonnet-5 / claude-haiku-4-5 / glm-5 (JANGAN hard-code)'),
                TextInput::make('max_tokens')->label('Max tokens')->numeric()->default(3000),
                TextInput::make('temperature')->label('Temperature')->numeric()->step(0.1)->default(0.7),
                TextInput::make('timeout_s')->label('Timeout (saat)')->numeric()->default(90),
                KeyValue::make('meta')->label('Kadar harga (§8.8)')
                    ->keyLabel('Kunci')->valueLabel('Nilai')
                    ->default(['rate_in_per_mtok' => '', 'rate_out_per_mtok' => '', 'currency' => 'USD']),
                Toggle::make('is_active')->label('Aktif')->default(true),
                Toggle::make('is_default')->label('Default')->default(false)
                    ->helperText('Hanya satu provider boleh jadi default.'),
            ]);
    }
}
