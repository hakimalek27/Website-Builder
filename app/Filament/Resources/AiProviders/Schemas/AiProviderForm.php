<?php

namespace App\Filament\Resources\AiProviders\Schemas;

use App\Enums\AiVendor;
use App\Support\ModelRates;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class AiProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama')->required()->maxLength(80)
                    ->placeholder('cth: OpenRouter Utama'),

                // Pilih penyedia → auto-isi driver + base URL + cadangan model.
                Select::make('vendor')
                    ->label('Penyedia')
                    ->options(AiVendor::options())
                    ->default('anthropic')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set): void {
                        $vendor = AiVendor::tryFrom((string) $state);
                        if (! $vendor) {
                            return;
                        }
                        $set('driver', $vendor->driver()->value);
                        $set('base_url', $vendor->baseUrl());
                        $set('model', '');
                    })
                    ->helperText('Base URL & driver diisi automatik. Admin cuma perlu API key + model.'),

                Select::make('driver')->label('Driver (auto)')->required()
                    ->options(['anthropic' => 'Anthropic (/v1/messages)', 'openai_compatible' => 'OpenAI-compatible (/chat/completions)'])
                    ->default('anthropic')
                    ->helperText('Ditetapkan ikut penyedia — jarang perlu diubah.'),

                TextInput::make('base_url')->label('Base URL')
                    ->default(AiVendor::Anthropic->baseUrl())
                    ->placeholder('auto-isi ikut penyedia')
                    ->helperText('Diisi automatik. Edit hanya untuk Ollama / self-hosted / custom.'),

                TextInput::make('api_key')
                    ->label('API Key')
                    ->password()
                    ->revealable()
                    // Write-only: kosong = kekal nilai lama; paparan ••••+4 melalui placeholder.
                    ->dehydrated(fn ($state) => filled($state))
                    ->placeholder(fn ($record) => $record?->api_key ? '••••'.substr($record->api_key, -4) : 'Tampal secret key di sini')
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText(function (Get $get): ?HtmlString {
                        $url = AiVendor::tryFrom((string) $get('vendor'))?->apiKeyUrl();

                        return $url
                            ? new HtmlString('Dapatkan key: <a href="'.e($url).'" target="_blank" rel="noopener" class="underline">'.e($url).'</a>')
                            : null;
                    }),

                // Pilih model → auto-isi kadar harga (bila diketahui). Kadar kekal boleh diedit.
                TextInput::make('model')->label('Model')->required()
                    ->placeholder('pilih dari senarai atau taip sendiri')
                    ->datalist(fn (Get $get): array => AiVendor::tryFrom((string) $get('vendor'))?->models() ?? [])
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                        $rates = ModelRates::for((string) $get('vendor'), (string) $state);
                        if ($rates !== null) {
                            $set('meta', [
                                'rate_in_per_mtok' => (string) $rates['in'],
                                'rate_out_per_mtok' => (string) $rates['out'],
                                'currency' => 'USD',
                            ]);
                        }
                    })
                    ->helperText(function (Get $get): HtmlString {
                        $rates = ModelRates::for((string) $get('vendor'), (string) $get('model'));
                        $src = ModelRates::source((string) $get('vendor'));
                        if ($rates !== null) {
                            $s = 'Kadar auto: USD '.$rates['in'].' masuk / '.$rates['out'].' keluar per juta token (rujuk '.ModelRates::AS_OF.')';

                            return new HtmlString($src
                                ? $s.' — <a href="'.e($src).'" target="_blank" rel="noopener" class="underline">sumber</a>. Boleh diedit.'
                                : $s.'. Boleh diedit.');
                        }

                        return new HtmlString('Tiada kadar auto untuk model ini — isi kadar dalam "Kadar harga" di bawah (USD/juta token, §8.8).');
                    }),

                TextInput::make('max_tokens')->label('Max tokens')->numeric()->default(5000)
                    ->helperText('Model reasoning terbaru guna banyak token — 5000+ disyorkan.'),
                TextInput::make('temperature')->label('Temperature')->numeric()->step(0.1)->default(0.7),
                TextInput::make('timeout_s')->label('Timeout (saat)')->numeric()->default(90),
                KeyValue::make('meta')->label('Kadar harga (USD / juta token, §8.8)')
                    ->keyLabel('Kunci')->valueLabel('Nilai')
                    ->default(['rate_in_per_mtok' => '', 'rate_out_per_mtok' => '', 'currency' => 'USD']),
                Toggle::make('is_active')->label('Aktif')->default(true),
                Toggle::make('is_default')->label('Default')->default(false)
                    ->helperText('Hanya satu provider boleh jadi default. Menjana draf (Peringkat 2 saluran HTML).'),
                Toggle::make('is_prompt_engineer')->label('Jurutera Prompt (Peringkat 1)')->default(false)
                    ->helperText('Hanya satu. Menjana prompt lengkap untuk draf HTML; draf sebenar dijana oleh penyedia Default. Cadangan: OpenAI gpt-5.5.'),
            ]);
    }
}
