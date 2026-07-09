<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

/**
 * §5.3 — Settings page: gateway WhatsApp, cooldown, kuota lalai, tempoh token, email admin.
 */
class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Tetapan';

    protected static string|UnitEnum|null $navigationGroup = 'Konfigurasi';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.manage-settings';

    public string $whatsapp_gateway_url = '';

    public string $whatsapp_gateway_secret = '';

    public string $gen_cooldown_minutes = '5';

    public string $default_ai_quota = '3';

    public string $default_design_quota = '5';

    public string $invitation_default_days = '30';

    public string $admin_notify_email = '';

    public function mount(): void
    {
        $this->whatsapp_gateway_url = (string) Setting::get('whatsapp_gateway_url');
        $this->whatsapp_gateway_secret = (string) Setting::get('whatsapp_gateway_secret');
        $this->gen_cooldown_minutes = (string) (Setting::get('gen_cooldown_minutes') ?? '5');
        $this->default_ai_quota = (string) (Setting::get('default_ai_quota') ?? '3');
        $this->default_design_quota = (string) (Setting::get('default_design_quota') ?? '5');
        $this->invitation_default_days = (string) (Setting::get('invitation_default_days') ?? '30');
        $this->admin_notify_email = (string) (Setting::get('admin_notify_email') ?? '');
    }

    public function save(): void
    {
        Setting::put('whatsapp_gateway_url', $this->whatsapp_gateway_url ?: null);
        Setting::put('whatsapp_gateway_secret', $this->whatsapp_gateway_secret ?: null, encrypted: true);
        Setting::put('gen_cooldown_minutes', $this->gen_cooldown_minutes);
        Setting::put('default_ai_quota', $this->default_ai_quota);
        Setting::put('default_design_quota', $this->default_design_quota);
        Setting::put('invitation_default_days', $this->invitation_default_days);
        Setting::put('admin_notify_email', $this->admin_notify_email ?: null);

        Notification::make()->title('Tetapan disimpan')->success()->send();
    }
}
