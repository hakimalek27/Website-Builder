<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\WhatsappGateway;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * §5.3 — Tetapan: gateway WhatsApp (wassap.wehdah.my), kuota lalai, jemputan & notifikasi.
 *
 * Borang guna Filament Schema (Section + TextInput). PENTING: kelas Tailwind arbitrari
 * (grid-cols-2, space-y-8, border-gray-300…) dalam blade page TIDAK dikompil oleh tema
 * panel Filament, jadi borang HTML mentah akan runtuh. Komponen Filament render guna CSS
 * panel yang dijamin wujud → layout konsisten dengan seluruh admin.
 */
class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Tetapan';

    protected static ?string $title = 'Tetapan';

    protected static string|UnitEnum|null $navigationGroup = 'Konfigurasi';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.manage-settings';

    /** State borang (statePath). */
    public ?array $data = [];

    public function mount(): void
    {
        // whatsapp_api_key sengaja TIDAK dibaca (elak dedah rahsia dalam DOM).
        $this->form->fill([
            'whatsapp_gateway_url' => (string) Setting::get('whatsapp_gateway_url'),
            'whatsapp_api_key' => '',
            'whatsapp_session_id' => (string) Setting::get('whatsapp_session_id'),
            'admin_notify_phone' => (string) Setting::get('admin_notify_phone'),
            'gen_cooldown_minutes' => (string) (Setting::get('gen_cooldown_minutes') ?? '5'),
            'default_ai_quota' => (string) (Setting::get('default_ai_quota') ?? '3'),
            'default_design_quota' => (string) (Setting::get('default_design_quota') ?? '5'),
            'invitation_default_days' => (string) (Setting::get('invitation_default_days') ?? '30'),
            'admin_notify_email' => (string) (Setting::get('admin_notify_email') ?? ''),
            'draft_pipeline' => (string) (Setting::get('draft_pipeline') ?? 'shell'),
            'html_max_tokens' => (string) (Setting::get('html_max_tokens') ?? '30000'),
            'qa_auto_polish' => (string) (Setting::get('qa_auto_polish') ?? '1'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Gateway WhatsApp')
                    ->description('Sambungan ke wassap.wehdah.my untuk makluman lead, hantaran borang & nota.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('whatsapp_gateway_url')->label('URL Gateway (asas)')
                            ->url()->placeholder('https://wassap.wehdah.my'),
                        TextInput::make('whatsapp_api_key')->label('Kunci API (X-API-Key)')
                            ->password()->revealable()->placeholder('Biar kosong untuk kekalkan')
                            ->helperText('Ditampal sekali; kosong = tidak diubah.'),
                        TextInput::make('whatsapp_session_id')->label('ID Sesi Penghantar')
                            ->placeholder('Peranti 60174627287 (pilihan)'),
                        TextInput::make('admin_notify_phone')->label('Telefon Admin (notifikasi)')
                            ->placeholder('60189030363'),
                    ]),
                Section::make('Saluran Draf (Enjin Penjanaan)')
                    ->description('Pilih cara draf dijana. HTML dua-peringkat: GPT jana prompt lengkap → GLM jana laman HTML statik yang boleh diklik.')
                    ->columns(2)
                    ->schema([
                        Select::make('draft_pipeline')->label('Saluran draf')
                            ->options([
                                'template' => 'Templat rujukan (galeri — tanpa AI)',
                                'shell' => 'Shell (JSON + templat) — klasik',
                                'html' => 'HTML dua-peringkat (GPT → GLM)',
                            ])
                            ->native(false)
                            ->helperText('Templat: PIC pilih rujukan reka bentuk dari galeri; admin bina manual (tiada penjanaan AI). Shell/HTML: penjanaan draf AI.'),
                        TextInput::make('html_max_tokens')->label('Had token draf HTML')
                            ->numeric()->minValue(1000)
                            ->helperText('Output HTML besar — 30000 disyorkan. Hanya untuk saluran HTML.'),
                        Select::make('qa_auto_polish')->label('Auto-polish QA (§Fasa 15)')
                            ->options(['1' => 'Hidup — 1 pusingan pembaikan automatik', '0' => 'Mati'])
                            ->native(false)
                            ->helperText('Bila QA kesan kualiti bawah piawai, sistem hantar 1 pusingan pembaikan ke penjana. Tidak makan kuota PIC (+1 panggilan P2).'),
                    ]),
                Section::make('Penjanaan & Kuota')
                    ->description('Kawalan cooldown penjanaan draf & kuota lalai projek baharu.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('gen_cooldown_minutes')->label('Cooldown jana (minit)')
                            ->numeric()->minValue(0),
                        TextInput::make('default_ai_quota')->label('Kuota AI lalai')
                            ->numeric()->minValue(1),
                        TextInput::make('default_design_quota')->label('Kuota render reka lalai')
                            ->numeric()->minValue(1),
                    ]),
                Section::make('Jemputan & Notifikasi')
                    ->description('Tempoh sah token jemputan & e-mel admin (fallback bila WhatsApp gagal).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('invitation_default_days')->label('Tempoh token lalai (hari)')
                            ->numeric()->minValue(1),
                        TextInput::make('admin_notify_email')->label('E-mel notifikasi admin')
                            ->email(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->persist($this->form->getState());
        Notification::make()->title('Tetapan disimpan')->success()->send();
    }

    /** Uji Hantar WhatsApp — hantar sebenar ke telefon admin (§13). */
    public function testWhatsapp(): void
    {
        $state = $this->form->getState();
        $this->persist($state);

        $phone = (string) ($state['admin_notify_phone'] ?? '');
        if (blank($phone)) {
            Notification::make()->title('Telefon admin belum diisi')->warning()->send();

            return;
        }

        $ok = app(WhatsappGateway::class)->send(
            $phone,
            'Ujian sambungan REKA — jika anda terima mesej ini, gateway WhatsApp berfungsi.',
            null,
            'settings.test',
        );

        $ok
            ? Notification::make()->title('Ujian dihantar')->body('Mesej ujian dihantar ke '.$phone)->success()->send()
            : Notification::make()->title('Ujian gagal')->body('Gateway tidak menerima mesej. Semak URL & kunci API.')->danger()->send();
    }

    /** @param  array<string,mixed>  $s */
    private function persist(array $s): void
    {
        Setting::put('whatsapp_gateway_url', ($s['whatsapp_gateway_url'] ?? null) ?: null);
        Setting::put('whatsapp_session_id', ($s['whatsapp_session_id'] ?? null) ?: null);
        Setting::put('admin_notify_phone', ($s['admin_notify_phone'] ?? null) ?: null);
        // Kunci API hanya dikemas kini bila diisi (kosong = kekalkan yang sedia ada).
        if (filled($s['whatsapp_api_key'] ?? null)) {
            Setting::put('whatsapp_api_key', $s['whatsapp_api_key'], encrypted: true);
        }

        Setting::put('gen_cooldown_minutes', (string) max(0, (int) ($s['gen_cooldown_minutes'] ?? 0)));
        Setting::put('default_ai_quota', (string) max(1, (int) ($s['default_ai_quota'] ?? 1)));
        Setting::put('default_design_quota', (string) max(1, (int) ($s['default_design_quota'] ?? 1)));
        Setting::put('invitation_default_days', (string) max(1, (int) ($s['invitation_default_days'] ?? 1)));
        Setting::put('admin_notify_email', ($s['admin_notify_email'] ?? null) ?: null);

        // Saluran draf — whitelist (nilai tak sah jatuh ke 'shell' selamat).
        $pipeline = in_array($s['draft_pipeline'] ?? 'shell', ['shell', 'html', 'template'], true) ? $s['draft_pipeline'] : 'shell';
        Setting::put('draft_pipeline', $pipeline);
        Setting::put('html_max_tokens', (string) max(1000, (int) ($s['html_max_tokens'] ?? 30000)));
        Setting::put('qa_auto_polish', in_array($s['qa_auto_polish'] ?? '1', ['0', '1'], true) ? $s['qa_auto_polish'] : '1');
    }
}
