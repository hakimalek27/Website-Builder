<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\WhatsappGateway;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

/**
 * §5.3 — Tetapan: gateway WhatsApp (wassap.wehdah.my), kuota lalai, jemputan & notifikasi.
 */
class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Tetapan';

    protected static ?string $title = 'Tetapan';

    protected static string|UnitEnum|null $navigationGroup = 'Konfigurasi';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.manage-settings';

    // WhatsApp (wassap.wehdah.my)
    public string $whatsapp_gateway_url = '';

    public string $whatsapp_api_key = '';        // TIDAK dipra-isi (rahsia); kosong = kekal

    public string $whatsapp_session_id = '';

    public string $admin_notify_phone = '';

    // Penjanaan & kuota
    public string $gen_cooldown_minutes = '5';

    public string $default_ai_quota = '3';

    public string $default_design_quota = '5';

    // Jemputan & notifikasi
    public string $invitation_default_days = '30';

    public string $admin_notify_email = '';

    public function mount(): void
    {
        $this->whatsapp_gateway_url = (string) Setting::get('whatsapp_gateway_url');
        $this->whatsapp_session_id = (string) Setting::get('whatsapp_session_id');
        $this->admin_notify_phone = (string) Setting::get('admin_notify_phone');
        $this->gen_cooldown_minutes = (string) (Setting::get('gen_cooldown_minutes') ?? '5');
        $this->default_ai_quota = (string) (Setting::get('default_ai_quota') ?? '3');
        $this->default_design_quota = (string) (Setting::get('default_design_quota') ?? '5');
        $this->invitation_default_days = (string) (Setting::get('invitation_default_days') ?? '30');
        $this->admin_notify_email = (string) (Setting::get('admin_notify_email') ?? '');
        // whatsapp_api_key sengaja TIDAK dibaca (elak dedah rahsia dalam DOM).
    }

    public function save(): void
    {
        $this->persist();
        Notification::make()->title('Tetapan disimpan')->success()->send();
    }

    /** Uji Hantar WhatsApp — hantar sebenar ke telefon admin (§13). */
    public function testWhatsapp(): void
    {
        $this->persist();

        if (blank($this->admin_notify_phone)) {
            Notification::make()->title('Telefon admin belum diisi')->warning()->send();

            return;
        }

        $ok = app(WhatsappGateway::class)->send(
            $this->admin_notify_phone,
            'Ujian sambungan REKA — jika anda terima mesej ini, gateway WhatsApp berfungsi.',
            null,
            'settings.test',
        );

        $ok
            ? Notification::make()->title('Ujian dihantar')->body('Mesej ujian dihantar ke '.$this->admin_notify_phone)->success()->send()
            : Notification::make()->title('Ujian gagal')->body('Gateway tidak menerima mesej. Semak URL & kunci API.')->danger()->send();
    }

    private function persist(): void
    {
        Setting::put('whatsapp_gateway_url', $this->whatsapp_gateway_url ?: null);
        Setting::put('whatsapp_session_id', $this->whatsapp_session_id ?: null);
        Setting::put('admin_notify_phone', $this->admin_notify_phone ?: null);
        // Kunci API hanya dikemas kini bila diisi (kosong = kekalkan yang sedia ada).
        if (filled($this->whatsapp_api_key)) {
            Setting::put('whatsapp_api_key', $this->whatsapp_api_key, encrypted: true);
        }

        Setting::put('gen_cooldown_minutes', (string) max(0, (int) $this->gen_cooldown_minutes));
        Setting::put('default_ai_quota', (string) max(1, (int) $this->default_ai_quota));
        Setting::put('default_design_quota', (string) max(1, (int) $this->default_design_quota));
        Setting::put('invitation_default_days', (string) max(1, (int) $this->invitation_default_days));
        Setting::put('admin_notify_email', $this->admin_notify_email ?: null);
    }
}
