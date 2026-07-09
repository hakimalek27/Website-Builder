<?php

namespace App\Livewire\Wizard;

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Models\DesignPackage;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\ProjectPage;
use App\Models\ProjectSection;
use App\Support\PageCatalog;
use App\Support\PresetMatrix;
use App\Support\WizardSteps;
use App\Support\ZoneLookup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * Enjin wizard (§6, §5.2 P2). Satu komponen berparameter untuk semua langkah.
 * - Autosave §6.13 (wire:model.blur.debounce → updated → save).
 * - Validasi LEMBUT (papar ralat, jangan halang simpan).
 * - Hooks: L0 apply preset §6.11 (sekali), L1 sync projects, L2 sync project_design.
 */
class WizardStep extends Component
{
    public string $token;

    public int $step;

    /** @var array<string, mixed> */
    public array $data = [];

    public ?string $savedAt = null;

    public bool $readOnly = false;

    public string $mosqueName = '';

    public function mount(string $token, int $step): void
    {
        $this->token = $token;
        $this->step = $step;

        $project = $this->resolveProject();
        $this->readOnly = $project->isFrozen();
        $this->mosqueName = $project->mosque_name;

        // PIC membuka wizard buat kali pertama → in_progress.
        if ($project->status === ProjectStatus::Invited) {
            $project->transitionTo(ProjectStatus::InProgress, 'pic');
        }

        $section = $project->sections()->where('section_key', $this->sectionKey())->first();
        $this->data = $section?->data ?? [];

        if ($this->step === 3 && ! isset($this->data['pages'])) {
            // Inisialisasi dari project_pages (preset §6.11 telah diapply di L0).
            $this->data['pages'] = $project->pages()->where('enabled', true)->pluck('page_key')->all();
        }

        if ($this->step === 4) {
            $this->applyPanelDefaults($project);
        }
    }

    /** Pra-isi & lalai panel L4 (§6 L4) — cth infaq 4 kategori, toggle waktu_solat. */
    protected function applyPanelDefaults(Project $project): void
    {
        $this->data['panels'] ??= [];

        foreach ($this->activePanels($project) as $pageKey) {
            $this->data['panels'][$pageKey] ??= [];

            foreach (PageCatalog::panels()[$pageKey] as $field) {
                $path = $field['key'];
                // Pra-isi kategori infaq.
                if (($field['prefill'] ?? null) === 'infaq' && empty($this->data['panels'][$pageKey][$path])) {
                    $this->data['panels'][$pageKey][$path] = PageCatalog::infaqPrefill();
                }
                // Lalai checkbox.
                if ($field['type'] === 'checkbox' && ($field['default'] ?? false) && ! array_key_exists($path, $this->data['panels'][$pageKey])) {
                    $this->data['panels'][$pageKey][$path] = true;
                }
            }
        }
    }

    /** page_key aktif (project_pages enabled) yang mempunyai panel L4. */
    protected function activePanels(Project $project): array
    {
        $enabled = $project->pages()->where('enabled', true)->pluck('page_key')->all();

        return array_values(array_intersect($enabled, PageCatalog::pagesWithPanel()));
    }

    protected function sectionKey(): string
    {
        return WizardSteps::sectionKey($this->step);
    }

    /** Re-resolusi projek dari token setiap permintaan (§11.1). */
    protected function resolveProject(): Project
    {
        $invitation = Invitation::query()
            ->where('token_hash', Invitation::hashToken($this->token))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return $invitation->project;
    }

    /** Autosave: dipanggil bila mana-mana medan data.* dikemas kini. */
    public function updated(string $name): void
    {
        if (str_starts_with($name, 'data.')) {
            $this->save();
        }
    }

    public function save(): void
    {
        $project = $this->resolveProject();

        if ($project->isFrozen()) {
            $this->readOnly = true;

            return; // baca-sahaja selepas approved
        }

        // Validasi LEMBUT — papar ralat, jangan halang simpan (§6.13).
        $this->resetErrorBag();
        $validator = Validator::make($this->data, $this->rulesFor(), $this->validationMessages());
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $messages) {
                $this->addError('data.'.$key, $messages[0]);
            }
        }

        // Upsert section.
        ProjectSection::updateOrCreate(
            ['project_id' => $project->id, 'section_key' => $this->sectionKey()],
            [
                'data' => $this->data,
                'completed_at' => $validator->fails() ? null : now(),
            ],
        );

        $this->afterSave($project);
        $this->savedAt = now()->format('H:i');
    }

    /** Hook selepas simpan setiap langkah. */
    protected function afterSave(Project $project): void
    {
        match ($this->step) {
            0 => $this->afterStep0($project),
            1 => $this->afterStep1($project),
            2 => $this->afterStep2($project),
            3 => $this->afterStep3($project),
            default => null,
        };
    }

    /** L3 — segerak pilihan halaman ke project_pages (§6 L3). */
    protected function afterStep3(Project $project): void
    {
        $selected = collect($this->data['pages'] ?? [])
            ->merge(PageCatalog::MANDATORY)   // utama & hubungi kekal wajib
            ->unique()
            ->values();

        $sort = 0;
        foreach (array_keys(PageCatalog::meta()) as $pageKey) {
            $project->pages()->updateOrCreate(
                ['page_key' => $pageKey],
                ['enabled' => $selected->contains($pageKey), 'sort' => $sort++],
            );
        }

        // Halaman custom (max 3).
        foreach (array_slice($this->data['custom'] ?? [], 0, 3) as $i => $custom) {
            if (filled($custom['name'] ?? null)) {
                $project->pages()->updateOrCreate(
                    ['page_key' => 'custom_'.$i],
                    ['enabled' => true, 'custom_name' => $custom['name'], 'sort' => 100 + $i],
                );
            }
        }
    }

    protected function afterStep0(Project $project): void
    {
        $tier = Tier::tryFrom($this->data['tier'] ?? '') ?? $project->tier;
        $isGov = (bool) ($this->data['is_gov'] ?? false);

        $project->update(['tier' => $tier, 'is_gov' => $isGov]);

        // Apply preset §6.11 HANYA jika Langkah 3 belum pernah disentuh.
        $step3Touched = $project->sections()->where('section_key', 'step_3')->exists();
        if (! $step3Touched) {
            $enabled = PresetMatrix::pagesFor($tier, $isGov);
            $sort = 0;
            foreach ($enabled as $pageKey) {
                ProjectPage::updateOrCreate(
                    ['project_id' => $project->id, 'page_key' => $pageKey],
                    ['enabled' => true, 'sort' => $sort++],
                );
            }
        }
    }

    protected function afterStep1(Project $project): void
    {
        $updates = array_filter([
            'mosque_name' => $this->data['official_name'] ?? null,
            'short_name' => $this->data['short_name'] ?? null,
            'state' => $this->data['state'] ?? null,
            'jakim_zone' => $this->data['jakim_zone'] ?? null,
        ], fn ($v) => filled($v));

        if ($updates !== []) {
            $project->update($updates);
        }
    }

    protected function afterStep2(Project $project): void
    {
        $packageKey = $this->data['design_package'] ?? null;
        if (blank($packageKey)) {
            return;
        }

        $overrides = array_filter([
            'palette' => $this->data['palette'] ?? null,
            'font_pair' => $this->data['font_pair'] ?? null,
            'icon_style' => $this->data['icon_style'] ?? null,
            'layout' => $this->data['layout_home'] ?? null,
            'islamic_elements' => $this->data['islamic_elements'] ?? null,
            'mood' => $this->data['mood'] ?? null,
        ], fn ($v) => filled($v) || $v === []);

        $project->design()->updateOrCreate(
            ['project_id' => $project->id],
            ['package_key' => $packageKey, 'overrides' => $overrides ?: null],
        );
    }

    // --- Navigasi ---

    public function next()
    {
        $this->save();
        $target = min($this->step + 1, WizardSteps::count() - 1);

        return redirect()->route('pic.step', ['token' => $this->token, 'step' => $target]);
    }

    public function back()
    {
        $this->save();
        $target = max($this->step - 1, 0);

        return redirect()->route('pic.step', ['token' => $this->token, 'step' => $target]);
    }

    public function saveAndExit()
    {
        $this->save();

        return redirect()->route('pic.home', ['token' => $this->token]);
    }

    // --- Repeater generik (§6 L4/L5) ---

    public function addRow(string $path): void
    {
        $rows = Arr::get($this->data, $path, []);
        $rows[] = [];
        Arr::set($this->data, $path, array_values($rows));
        $this->save();
    }

    public function removeRow(string $path, int $index): void
    {
        $rows = Arr::get($this->data, $path, []);
        unset($rows[$index]);
        Arr::set($this->data, $path, array_values($rows));
        $this->save();
    }

    /** Muat templat statik boleh-edit (§6 L4). */
    public function loadTemplate(string $path, string $template): void
    {
        Arr::set($this->data, $path, __('templates.'.$template));
        $this->save();
    }

    /** Muat 8 soalan lazim biasa ke panel FAQ. */
    public function loadFaqCommon(string $path): void
    {
        Arr::set($this->data, $path, trans('templates.faq_common'));
        $this->save();
    }

    /** @return array<string, mixed> */
    protected function rulesFor(): array
    {
        return match ($this->step) {
            0 => [
                'tier' => ['required', 'in:surau_ringkas,masjid_kariah,masjid_besar'],
                'is_gov' => ['boolean'],
            ],
            1 => [
                'official_name' => ['required', 'string', 'max:150'],
                'short_name' => ['nullable', 'string', 'max:40'],
                'address_line1' => ['required', 'string', 'max:200'],
                'address_line2' => ['nullable', 'string', 'max:200'],
                'postcode' => ['required', 'regex:/^[0-9]{5}$/'],
                'city' => ['required', 'string', 'max:100'],
                'state' => ['required', 'in:'.implode(',', config('reka.states'))],
                'jakim_zone' => ['required', 'string', 'max:5'],
                'authority' => ['required', 'string', 'max:100'],
                'established_year' => ['nullable', 'integer', 'min:1800', 'max:2026'],
                'capacity' => ['nullable', 'integer', 'min:1'],
                'gps' => ['required', 'string', function ($attr, $value, $fail) {
                    if (! $this->isValidMalaysiaGps($value)) {
                        $fail('Koordinat GPS tidak sah atau di luar julat Malaysia.');
                    }
                }],
                'phone_primary' => ['required', 'regex:/^[0-9+\-\s]{7,20}$/'],
                'email' => ['required', 'email', 'max:150'],
                'logo_status' => ['required', 'in:ada,perlu_direka,teks_sahaja'],
            ],
            2 => [
                'design_package' => ['required', 'in:warisan_hijau,biru_nilam,emas_kubah,teal_kontemporari,marun_agung'],
                'mood' => ['required', 'in:tenang_khusyuk,mesra_keluarga,megah_berwibawa'],
            ],
            4 => $this->rulesForStep4(),
            5 => $this->rulesForStep5(),
            default => [],
        };
    }

    /** Validasi L4 dibina dinamik dari skema panel aktif (§6 L4). */
    protected function rulesForStep4(): array
    {
        $rules = [];
        $project = $this->resolveProject();

        foreach ($this->activePanels($project) as $pageKey) {
            foreach (PageCatalog::panels()[$pageKey] as $field) {
                $base = "panels.{$pageKey}.{$field['key']}";
                $type = $field['type'];

                if (in_array($type, ['text', 'textarea', 'number', 'email', 'url', 'select', 'radio'], true)) {
                    $r = [];
                    if ($field['required'] ?? false) {
                        $r[] = 'required';
                    } else {
                        $r[] = 'nullable';
                    }
                    if (isset($field['max'])) {
                        $r[] = 'max:'.$field['max'];
                    }
                    if (isset($field['options'])) {
                        $r[] = 'in:'.implode(',', array_keys($field['options']));
                    }
                    $rules[$base] = $r;
                }

                // Repeater: enum + required subfield DIKUNCI (cth peringkat Quran).
                if ($type === 'repeater' && isset($field['item'])) {
                    foreach ($field['item'] as $sub) {
                        $subBase = "{$base}.*.{$sub['key']}";
                        $r = ['nullable'];
                        if (isset($sub['options'])) {
                            $r[] = 'in:'.implode(',', array_keys($sub['options']));
                        }
                        if (isset($sub['max'])) {
                            $r[] = 'max:'.$sub['max'];
                        }
                        $rules[$subBase] = $r;
                    }
                }
            }

            // Galeri: consent WAJIB jika ada fail (§6 L4).
            if ($pageKey === 'galeri' && ! empty($this->data['panels']['galeri']['images'] ?? [])) {
                $rules['panels.galeri.consent'] = ['accepted'];
            }
        }

        return $rules;
    }

    /** Validasi L5 (§6 L5). */
    protected function rulesForStep5(): array
    {
        $rules = [
            'cms_updater' => ['required', 'in:ajk_sendiri,urus_azan,jarang'],
        ];

        // payment_gateway WAJIB jika infaq ditanda (§6.12).
        if ($this->resolveProject()->pages()->where('page_key', 'infaq')->where('enabled', true)->exists()) {
            $rules['payment_gateway'] = ['required', 'in:toyyibpay,billplz,duitnow_qr_statik,fpx_korporat,manual_bank'];
        }

        return $rules;
    }

    /** @return array<string, string> */
    protected function validationMessages(): array
    {
        return [
            'postcode.regex' => 'Poskod mesti 5 angka.',
            'phone_primary.regex' => 'Nombor telefon tidak sah.',
            'tier.required' => 'Sila pilih jenis masjid.',
            'design_package.required' => 'Sila pilih pakej reka bentuk.',
            'mood.required' => 'Sila pilih nada penulisan.',
        ];
    }

    public function isValidMalaysiaGps(?string $value): bool
    {
        if (blank($value) || ! str_contains($value, ',')) {
            return false;
        }
        [$lat, $lng] = array_map('trim', explode(',', $value, 2));
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return false;
        }
        $lat = (float) $lat;
        $lng = (float) $lng;

        return $lat >= 0.8 && $lat <= 7.5 && $lng >= 99.5 && $lng <= 119.5;
    }

    /** @return array<string, string> */
    public function zoneOptions(): array
    {
        return ZoneLookup::forState($this->data['state'] ?? null);
    }

    /** Tokens reka bentuk berkesan untuk pratonton hidup (§7.5). */
    public function previewTokens(): array
    {
        $key = $this->data['design_package'] ?? 'warisan_hijau';
        $package = DesignPackage::where('key', $key)->first();
        $tokens = $package?->tokens ?? [];

        if (! empty($this->data['palette']) && is_array($this->data['palette'])) {
            $tokens = array_merge($tokens, $this->data['palette']);
        }

        return $tokens;
    }

    /** Fon berkesan untuk pratonton (pakej default atau font_pair override §7.4). */
    public function previewFonts(): array
    {
        $pairs = [
            'A' => ['body' => 'Plus Jakarta Sans', 'display' => 'Cormorant Garamond'],
            'B' => ['body' => 'Inter', 'display' => 'Playfair Display'],
            'C' => ['body' => 'Figtree', 'display' => 'Lora'],
            'D' => ['body' => 'IBM Plex Sans', 'display' => 'IBM Plex Serif'],
        ];

        if (! empty($this->data['font_pair']) && isset($pairs[$this->data['font_pair']])) {
            return $pairs[$this->data['font_pair']];
        }

        $key = $this->data['design_package'] ?? 'warisan_hijau';
        $package = DesignPackage::where('key', $key)->first();

        return $package?->fonts ?? $pairs['A'];
    }

    public function render()
    {
        $viewData = [
            'stepMeta' => WizardSteps::all()[$this->step],
            'totalSteps' => WizardSteps::count(),
            'designPackages' => $this->step === 2
                ? DesignPackage::where('is_active', true)->get()
                : collect(),
        ];

        if (in_array($this->step, [3, 4], true)) {
            $viewData['activePanels'] = $this->activePanels($this->resolveProject());
        }

        return view('livewire.wizard.wizard-step', $viewData);
    }
}
