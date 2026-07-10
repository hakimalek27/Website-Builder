<?php

namespace App\Livewire\Wizard;

use App\Enums\ProjectStatus;
use App\Enums\Tier;
use App\Exceptions\UploadException;
use App\Models\Asset;
use App\Models\DesignPackage;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\ProjectPage;
use App\Models\ProjectSection;
use App\Services\UploadService;
use App\Support\FontPairs;
use App\Support\Moods;
use App\Support\PageCatalog;
use App\Support\PaletteDeriver;
use App\Support\PresetMatrix;
use App\Support\WizardSteps;
use App\Support\ZoneLookup;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Enjin wizard (§6, §5.2 P2). Satu komponen berparameter untuk semua langkah.
 * - Autosave §6.13 (wire:model.blur.debounce → updated → save).
 * - Validasi LEMBUT (papar ralat, jangan halang simpan).
 * - Hooks: L0 apply preset §6.11 (sekali), L1 sync projects, L2 sync project_design.
 */
class WizardStep extends Component
{
    use WithFileUploads;

    public string $token;

    public int $step;

    /** @var array<string, mixed> */
    public array $data = [];

    /** Muat naik sementara dikunci oleh path rel. */
    public array $files = [];

    public ?string $savedAt = null;

    public bool $readOnly = false;

    public string $mosqueName = '';

    public bool $isNgo = false;

    public string $orgNoun = 'masjid';

    public function mount(string $token, int $step): void
    {
        $this->token = $token;
        $this->step = $step;

        $project = $this->resolveProject();
        $this->readOnly = $project->isFrozen();
        $this->mosqueName = $project->mosque_name;
        $this->isNgo = $project->tier->isNgo();
        $this->orgNoun = $project->tier->orgNoun();

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

        if ($this->step === 9) {
            // Pra-isi identiti PIC dari jemputan (boleh dibetulkan).
            $invitation = $project->invitation;
            $this->data['pic_name'] ??= $invitation?->pic_name;
            $this->data['pic_phone'] ??= $invitation?->pic_phone;
        }
    }

    /** Pra-isi & lalai panel L4 (§6 L4) — cth infaq 4 kategori, toggle waktu_solat. */
    protected function applyPanelDefaults(Project $project): void
    {
        $this->data['panels'] ??= [];
        $panels = PageCatalog::panelsFor($project->tier);

        foreach ($this->activePanels($project) as $pageKey) {
            $this->data['panels'][$pageKey] ??= [];

            foreach ($panels[$pageKey] as $field) {
                $path = $field['key'];
                // Pra-isi kategori infaq / derma.
                if (($field['prefill'] ?? null) === 'infaq' && empty($this->data['panels'][$pageKey][$path])) {
                    $this->data['panels'][$pageKey][$path] = PageCatalog::infaqPrefill();
                }
                if (($field['prefill'] ?? null) === 'derma' && empty($this->data['panels'][$pageKey][$path])) {
                    $this->data['panels'][$pageKey][$path] = PageCatalog::dermaPrefill();
                }
                // Lalai checkbox.
                if ($field['type'] === 'checkbox' && ($field['default'] ?? false) && ! array_key_exists($path, $this->data['panels'][$pageKey])) {
                    $this->data['panels'][$pageKey][$path] = true;
                }
            }
        }
    }

    /** page_key aktif (project_pages enabled) yang mempunyai panel L4 (ikut tier). */
    protected function activePanels(Project $project): array
    {
        $enabled = $project->pages()->where('enabled', true)->pluck('page_key')->all();

        return array_values(array_intersect($enabled, array_keys(PageCatalog::panelsFor($project->tier))));
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
        if (! str_starts_with($name, 'data.')) {
            return;
        }

        $this->save();

        // Elak morph DOM penuh pada simpanan skalar (menaip/select) — inilah punca
        // dropdown L4 tertutup sendiri. Radio/checkbox & langkah reaktif tetap render
        // (perlu untuk showIf + pratonton hidup). Chip "Disimpan" dikemas kini via event.
        if (! $this->needsRender($name)) {
            $this->skipRender();
        }
    }

    /** Perlukah render penuh selepas medan ini disimpan? (elak morph tak perlu §6.13) */
    protected function needsRender(string $name): bool
    {
        // Langkah reaktif: preset halaman (0/3), pratonton reka hidup (2).
        if (in_array($this->step, [0, 2, 3], true)) {
            return true;
        }

        $key = substr($name, strlen('data.'));
        $firstSegment = explode('.', $key)[0];

        // Medan skalar yang mengawal blok bersyarat merentas langkah.
        if (in_array($firstSegment, ['state', 'logo_status', 'hero_mode', 'domain_status', 'cms_updater', 'payment_gateway'], true)) {
            return true;
        }

        // Langkah 4: hanya kawalan .live (radio/checkbox/facility_checklist) memacu showIf.
        if ($this->step === 4 && str_starts_with($key, 'panels.')) {
            return $this->step4FieldNeedsRender($key);
        }

        return false;
    }

    /** Cari jenis medan panel L4 dari path; render hanya untuk pemacu showIf. */
    protected function step4FieldNeedsRender(string $key): bool
    {
        $parts = explode('.', $key);          // panels.{page}.{field}[.{i}.{sub}]
        $pageKey = $parts[1] ?? null;
        $fieldKey = $parts[2] ?? null;
        if ($pageKey === null || $fieldKey === null) {
            return true;                       // selamat: render
        }

        foreach ($this->panelSchema($pageKey) as $field) {
            if (($field['key'] ?? null) === $fieldKey) {
                return in_array($field['type'] ?? '', ['radio', 'checkbox', 'facility_checklist'], true);
            }
        }

        return false;
    }

    /** Skema panel L4 untuk page_key (ikut tier projek). */
    protected function panelSchema(string $pageKey): array
    {
        return PageCatalog::panelsFor($this->resolveProject()->tier)[$pageKey] ?? [];
    }

    /** Had bilangan imej hero (§6 L6). */
    private const HERO_MAX = 3;

    /** Muat naik fail (§11.4): proses melalui UploadService → Asset → rujukan dalam data. */
    public function updatedFiles($value, string $key): void
    {
        $project = $this->resolveProject();
        if ($project->isFrozen() || $value === null) {
            return;
        }

        $files = is_array($value) ? array_values($value) : [$value];   // 'multiple' → array
        $service = app(UploadService::class);
        $kind = $this->kindFor($key);
        $dataPath = $this->dataPathFor($key);
        $multi = $this->isMultiTarget($key);
        $errors = [];

        foreach ($files as $file) {
            // Kuatkuasa maksimum imej hero.
            if ($key === 'hero' && count(Arr::get($this->data, $dataPath, [])) >= self::HERO_MAX) {
                $errors[] = 'Maksimum '.self::HERO_MAX.' imej hero.';
                break;
            }

            try {
                $asset = $service->store($file, $kind, $project);
            } catch (UploadException $e) {
                $errors[] = $e->getMessage();

                continue;
            }

            $ref = ['asset_id' => $asset->id, 'path' => $asset->path, 'name' => $asset->original_name];

            if ($multi) {
                $existing = Arr::get($this->data, $dataPath, []);
                $existing[] = $ref;
                Arr::set($this->data, $dataPath, $existing);
            } else {
                Arr::set($this->data, $dataPath, $ref);
            }
        }

        Arr::forget($this->files, $key);
        $this->save();

        // addError SELEPAS save() (yang reset error bag) supaya ralat muat naik kekal dipapar.
        foreach (array_unique($errors) as $msg) {
            $this->addError("files.{$key}", $msg);
        }
    }

    /** Buang satu imej hero (padam Asset + fail fizikal). */
    public function removeHeroFile(int $index): void
    {
        $project = $this->resolveProject();
        if ($project->isFrozen()) {
            return;
        }

        $files = Arr::get($this->data, 'hero_files', []);
        if (! isset($files[$index])) {
            return;
        }

        $ref = $files[$index];
        if (! empty($ref['asset_id'])) {
            $asset = Asset::where('project_id', $project->id)->find($ref['asset_id']);
            if ($asset !== null) {
                Storage::disk('local')->delete($asset->path);
                $asset->delete();
            }
        }

        unset($files[$index]);
        Arr::set($this->data, 'hero_files', array_values($files));
        $this->save();
    }

    private function kindFor(string $key): string
    {
        return match (true) {
            $key === 'logo' => 'logo',
            $key === 'hero' => 'hero',
            str_contains($key, 'galeri.images') => 'gallery',
            str_contains($key, 'qr_image') => 'qr',
            str_contains($key, 'form_pdf'), str_ends_with($key, '.file') => 'doc',
            str_contains($key, 'perutusan') => 'perutusan_photo',
            str_contains($key, 'facility') => 'facility_photo',
            str_contains($key, 'photo') => 'committee_photo',
            default => 'gallery',
        };
    }

    private function isMultiTarget(string $key): bool
    {
        return $key === 'hero' || str_contains($key, 'images');
    }

    private function dataPathFor(string $key): string
    {
        return match ($key) {
            'logo' => 'logo_file',
            'hero' => 'hero_files',
            default => $key,
        };
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

        // Kemas kini chip "Disimpan" walaupun render dilangkau (skipRender pada autosave skalar).
        $this->dispatch('wizard-saved', at: $this->savedAt);
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
        foreach (array_keys(PageCatalog::metaFor($project->tier)) as $pageKey) {
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

        // NGO/pertubuhan tiada konteks domain .gov.my.
        if ($tier->isNgo()) {
            $isGov = false;
        }

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

        // Mod custom → simpan token terbitan penuh (WCAG-selamat) sebagai palette override.
        $custom = $this->customPalettePreview();

        $overrides = array_filter([
            'palette' => $custom['tokens'] ?? ($this->data['palette'] ?? null),
            'font_pair' => $this->data['font_pair'] ?? null,
            'icon_style' => $this->data['icon_style'] ?? null,
            'layout' => $this->data['layout_home'] ?? null,
            'header_style' => $this->data['header_style'] ?? null,
            'footer_style' => $this->data['footer_style'] ?? null,
            'card_style' => $this->data['card_style'] ?? null,
            'divider' => $this->data['divider'] ?? null,
            'animations' => $this->data['animations'] ?? null,
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

        // Langkah terakhir → terus ke Semak & Hantar (bukan redirect ke diri sendiri).
        if ($this->step >= WizardSteps::count() - 1) {
            return redirect()->route('pic.semak', ['token' => $this->token]);
        }

        return redirect()->route('pic.step', ['token' => $this->token, 'step' => $this->step + 1]);
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
                'tier' => ['required', Rule::in(Tier::values())],
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
                'design_package' => ['required', Rule::exists('design_packages', 'key')],
                'mood' => ['required', Rule::in(Moods::keys())],
                'font_pair' => ['nullable', Rule::in(FontPairs::keys())],
                'layout_home' => ['nullable', 'string', 'max:40'],
                'header_style' => ['nullable', 'string', 'max:40'],
                'footer_style' => ['nullable', 'string', 'max:40'],
                'card_style' => ['nullable', 'string', 'max:40'],
                'divider' => ['nullable', 'string', 'max:40'],
                'animations' => ['nullable', 'boolean'],
                'palette_mode' => ['nullable', 'in:pakej,custom'],
                'custom_primary' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                'custom_accent' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            ],
            4 => $this->rulesForStep4(),
            5 => $this->rulesForStep5(),
            6 => [
                'hero_mode' => ['required', 'in:upload,perlu_fotografi,stok_sementara'],
            ],
            8 => [
                'domain_status' => ['required', 'in:ada,belum,gov_my'],
            ],
            9 => [
                'pic_name' => ['required', 'string', 'max:100'],
                'pic_position' => ['required', 'string', 'max:100'],
                'pic_phone' => ['required', 'string'],
                'consent_pdpa' => ['accepted'],
                'declare_truth_authority' => ['accepted'],
            ],
            default => [],
        };
    }

    /** Validasi L4 dibina dinamik dari skema panel aktif (§6 L4). */
    protected function rulesForStep4(): array
    {
        $rules = [];
        $project = $this->resolveProject();

        $panels = PageCatalog::panelsFor($project->tier);
        foreach ($this->activePanels($project) as $pageKey) {
            foreach ($panels[$pageKey] as $field) {
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

        $custom = $this->customPalettePreview();
        if ($custom !== null) {
            return array_merge($tokens, $custom['tokens']);
        }

        if (! empty($this->data['palette']) && is_array($this->data['palette'])) {
            $tokens = array_merge($tokens, $this->data['palette']);
        }

        return $tokens;
    }

    /**
     * Pratonton palet custom (mod custom + 2 hex sah) — token terbitan + bendera pelarasan WCAG.
     *
     * @return array{tokens: array<string,string>, adjusted: bool}|null
     */
    public function customPalettePreview(): ?array
    {
        if (($this->data['palette_mode'] ?? 'pakej') !== 'custom') {
            return null;
        }

        $primary = $this->data['custom_primary'] ?? null;
        $accent = $this->data['custom_accent'] ?? null;
        if (! PaletteDeriver::isValidHex($primary) || ! PaletteDeriver::isValidHex($accent)) {
            return null;
        }

        return PaletteDeriver::derive($primary, $accent);
    }

    /** Fon berkesan untuk pratonton (nama fontsource di-hos-sendiri §7.4). */
    public function previewFonts(): array
    {
        $pair = $this->data['font_pair'] ?? null;
        if (! empty($pair) && FontPairs::has($pair)) {
            return FontPairs::previewFonts($pair);
        }

        // Fallback: font pakej → tukar ke nama pratonton fontsource (elak jatuh ke serif generik).
        $key = $this->data['design_package'] ?? 'warisan_hijau';
        $fonts = DesignPackage::where('key', $key)->first()?->fonts ?? FontPairs::fonts(FontPairs::DEFAULT);

        return [
            'body' => FontPairs::previewFamily($fonts['body'] ?? 'Plus Jakarta Sans'),
            'display' => FontPairs::previewFamily($fonts['display'] ?? 'Cormorant Garamond'),
        ];
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
            $project = $this->resolveProject();
            $viewData['activePanels'] = $this->activePanels($project);
            // Katalog mengikut tier — NGO ada kluster/panel/meta sendiri; masjid delegate verbatim.
            $viewData['pageMeta'] = PageCatalog::metaFor($project->tier);
            $viewData['pageClusters'] = PageCatalog::clustersFor($project->tier);
            $viewData['pagePanels'] = PageCatalog::panelsFor($project->tier);
        }

        return view('livewire.wizard.wizard-step', $viewData);
    }
}
