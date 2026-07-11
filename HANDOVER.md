# HANDOVER — REKA (Website Builder)

Kemas kini terakhir: **11 Julai 2026** · Branch: `main` · Remote: `github.com/hakimalek27/Website-Builder`

REKA — platform tempahan & penjanaan draf laman web **masjid, surau & NGO/pertubuhan Islam**.
Stack: **Laravel 13.19 · PHP 8.4 · Filament v4.11 · Livewire 3 · Tailwind 4 · Pest** (dev: SQLite).

---

## Status semasa

- **Fasa 0–10** + **rombakan UI/UX** + **Fasa 11** + **pembetulan pasca-audit** (`eca8f80`, `d027778`) + **fix AiClient OpenAI moden** (`f61ddec`) + **Fasa 12** (7 commit: `806d17a`→`ca674c1`) + **Fasa 13** (7 commit: `22b159a`→`0a5a172`) + **Fasa 14** (6 commit: `02c3e5c`→`9c534eb`).
- **260 ujian Pest hijau** (935 assertions) · `pint` bersih · `npm run build` bersih.
- Semua kerja **di-push ke `main`**.

### Fasa 14 — QA Auto, Salin Prompt, finish_reason, Varian Animasi & Audit Admin (11 Jul 2026)

Empat ciri dari senarai "cadangan masa depan" Fasa 13 + fix bug + audit admin.

1. **`02c3e5c` W1 fix bug** — `Setting::putIfMissing()` (guard `exists()`, bukan `get()` — nilai null yang sah tidak ditindih); `SettingsSeeder` semua `put`→`putIfMissing` (seed semula pada DB terkonfigurasi **tidak lagi memadam kunci API WhatsApp**). Migration `is_prompt_engineer` dijalankan pada DB dev — betulkan QueryException "no such column" semasa edit Penyedia AI.
2. **`2dc1285` W2 finish_reason** — `AiResult::$finishReason` (dinormalkan: OpenAI `length`, Anthropic `max_tokens`→`length`). `GenerateDraftJob::handleHtml` gagal awal bila P2 `finishReason==='length'` (sebelum validasi struktur) → makan 1 percubaan retry, jimat masa. Direkod dalam snapshot stage1/stage2; ProjectInfolist papar `henti: length`.
3. **`0af9ba9` W3 QA auto** — `DraftQaService` (saluran HTML): (a) setiap halaman dipilih hadir sebagai `<section id="{page_key}">` (fallback label/hero); (b) kontras token WCAG AA (`ink/bg`, `primary/bg`, `putih/primary`, **`primaryDark/accent`** — bukan accent/bg yang hiasan); (c) kontras inline AI (lapor). Wire ke Job (**Throwable-safe, TIDAK menghalang draf**) → `snapshot['qa']` + `Notifier::qaFlagged` (WA+mail admin, event `qa.flagged`) bila ada isu. Prompt diketatkan `id="{page_key}"` deterministik. `PaletteDeriver::MIN_CONTRAST` public.
4. **`37c7822` W4 Salin Prompt** — `Action 'salinPrompt'` di ViewProject header: salin prompt jurutera terkini ke papan klip (`$livewire->js(navigator.clipboard)`) + notifikasi. Visible bila prompt wujud. **Perlu konteks selamat** (localhost/HTTPS).
5. **`20ac88c` W5 varian animasi** — `tiada`/`fade`/`zoom` (dulu boolean tunggal). `DesignResolver::ANIMATIONS` + `animationVariant()` (pemetaan legasi bool→string). step-2 radio 3 pilihan; pratonton kini terima `:animations`, `data-animation` + `<style>` berskop (reduced-motion-gated, `rk-sec`) + `wire:key` main semula. shell keyframes `zoomIn`; prompt HTML beri arahan gaya CSS-only. **TIADA migration** (overrides JSON; projek lama bool kekal berfungsi).
6. **`9c534eb` W6 audit admin** — buang `CreateAction` mengelirukan di ListProjects (`canCreate=false`); `AdminAuditTest` boot-smoke SEMUA permukaan admin (Dashboard/4 resource/Settings/borang) tanpa 500 + regresi simpan AiProvider. Semakan visual langsung wizard L2 (radio + pratonton animasi) dalam browser sebenar.

**Keputusan owner:** varian animasi = naik taraf 3 pilihan · QA = laporan + WA/mail bila ada isu (draf tak dihalang). **Anggaran kos/jana kekal ~USD 0.44.**

**⚠ Audit admin LIVE (2FA) — untuk owner:** log masuk `/admin` + 2FA TOTP, kemudian semak setiap halaman/butang mengikut checklist W6 dalam pelan (`~/.claude/plans/kemaskini-ui-ux-setiap-cozy-muffin.md`). Audit peringkat-kod (boot-smoke) sudah lulus; ini pengesahan visual sahaja.

### Kadar kos penuh + demo E2E saluran HTML sebenar (11 Jul 2026, `9666542`)

- **`ModelRates` liputan penuh dropdown** (`9666542`) — kadar rasmi (rujuk laman vendor, Julai 2026) untuk SETIAP model dalam `AiVendor::models()`: Groq (5), Mistral (5), Google flash-lite/2.0, OpenRouter grok-4/llama-3.3-70b/qwen-2.5-72b, Zhipu glm-4.6/4.5-air/4.5-flash + anggaran glm-5.1/4.7/claude-fable-5. Ujian menjamin **setiap model dropdown auto-isi kadar**. Admin pilih model → kadar terisi → kos jana dikira.
- **Kos dikira dari `provider->meta` sahaja** (`GenerateDraftJob::cost()`) — BUKAN fallback ke ModelRates. Jika penyedia dicipta tanpa kadar (meta kosong) → kos jana = **USD 0.00** walaupun model dikenali. Penyelesaian: buka-simpan borang penyedia (auto-isi dari ModelRates) atau isi manual `rate_in_per_mtok`/`rate_out_per_mtok`.
- **Saluran boleh-tukar penyedia disahkan E2E** (jana PERKIB sebenar): mana-mana penyedia boleh jadi P1 (`is_prompt_engineer`) atau P2 (`is_default`). Diuji: **gpt-5.5→glm-5.2** (~USD 0.26) DAN **glm-5.2→gemini-2.5-pro** (~USD 0.15). Routing = `AiProvider::promptEngineer()` (P1) + `AiProvider::default()` (P2). QA lulus, 12/12 seksyen padan halaman PIC, prompt+kos+HTML dalam DB admin + brief.
- **⚠ Penyedia Jurutera Prompt (P1) perlu `timeout_s` ≥ 180** bila guna model perlahan (cth GLM-5.2 sebagai jurutera). Default 90s cukup untuk gpt-5.5 tapi GLM jana prompt panjang > 90s → `cURL 28 timeout` → gagal-terus (kuota tak dicaj). Naikkan `timeout_s` penyedia jurutera bila tukar ke model lebih perlahan.

### Fasa 13 — Saluran Draf HTML Dua-Peringkat (12 Jul 2026)

Saluran draf baharu: **Peringkat 1** penyedia "Jurutera Prompt" (OpenAI **gpt-5.5**) jana SATU prompt lengkap → **Peringkat 2** penyedia Default (OpenRouter **glm-5.2**) jana **draf HTML statik** (boleh klik). Mod boleh-tukar (`Setting draft_pipeline` shell/html) — saluran lama **kekal**.

1. **`22b159a` W1 asas** — `AiClient::complete(..., $options)` (json/max_tokens); `ai_providers.is_prompt_engineer` (satu, `AiProvider::promptEngineer()`); ManageSettings/Seeder (`draft_pipeline`=html, `html_max_tokens`=30000); `progress_steps_html`; ModelRates + `z-ai/glm-5.2`; `DB_QUEUE_RETRY_AFTER=360`.
2. **`47a53d9` W2 prompt** — `prompt-engineer-system.txt` + `html-draft-system.txt`; `HtmlPromptBuilder` (engineer/stage2/tweak — KONTEKS PII-min + reka bentuk DesignResolver + halaman + placeholder + nota); `PromptBuilder::minimizedContext()`; `DraftRenderer::verbatimFor()/heroImageFor()`.
3. **`f874ddd` W3 validasi/finisher** — `HtmlDraftValidator` (ekstrak doc, tolak Arab/JS/URL-luar/>400KB); `HtmlDraftFinisher` (ganti placeholder `[[...]]` verbatim + noindex+banner+watermark+"— DRAF"); 5 partial (bank/contact/ajk/prayer/verse).
4. **`dfd3126` W4 job** — `GenerateDraftJob` cabang `handleShell`/`handleHtml` (P1 jurutera→P2 HTML, retry HANYA P2); `DraftGenerationService::resolvePipeline()`+`pipelineMode()`; `Notifier::generationFailed`+WA admin.
5. **`f25b6f2` W5 PIC UX** — tweak HTML (base_generation_id; HTML **mentah bertoken** ke AI, bukan draf PII); `DesignRerenderService` guard html; JanaHub label saluran + baki + banner gagal; SemakController → `pic.jana`.
6. **`87f8145` W6 admin** — `AdminFileController::prompt()/draftDownload()` (route `admin.prompt`/`admin.draf.muat`); ProjectInfolist per-gen html (sumber/kos P1-P2/prompt/tweak); Brief + "Prompt Jurutera" + "Thread Tweak".
7. **`0a5a172` W7 pratonton** — design-preview varian header/footer/pembatas + fix `arabic_font` tak disalin ke overrides.

**Keputusan owner:** mod boleh-tukar · had tweak = kuota sedia ada (3 = 1 jana + 2 tweak) · **jurutera prompt gagal/tak diset → GAGAL TERUS** (mail+WA admin) · **TIADA migration enum** (pipeline dalam `input_snapshot`) · **PII-min §12.7 kedua-dua peringkat** (bank/telefon/nama tak ke AI — placeholder server) · CSP `raw` draf kekal. **Anggaran kos/jana penuh ~USD 0.44** (1 jana + 2 tweak).

### Fasa 12 — Visibiliti, Brief, Nota→AI, Kos Model & Pengayaan Prompt (11 Jul 2026)

Menyelesaikan 6 aduan product owner. Pelan: `~/.claude/plans/kemaskini-ui-ux-setiap-cozy-muffin.md`.

1. **`806d17a` W1 nav PIC** — `layouts/pic` + `components/pic/nav`: bar nav status-aware (Utama/Borang/Semak/Jana Draf/Draf/Status); `Project::latestDraft()`; baris "Draf terdahulu" JanaHub kini pautan; kad pintasan home. (Aduan: halaman Jana Draf tak boleh ditemui.)
2. **`578fd60` W1 deep-link WA** — `DraftGenerationService::request(picBaseUrl)` → `GenerateDraftJob` bina pautan draf sebenar dalam WA (bukan string mati); `GenerateDraftJob`+`SendWhatsappJob` `ShouldBeEncrypted`.
3. **`6a3908c` W5 kos** — `ModelRates` (harga rasmi USD/MTok: gpt-5.5 5/30, opus-4-8 5/25, glm-5.2 1.40/4.40 + lain); `AiProviderForm` auto-isi kadar bila model dipilih; label kos jelas **USD** (bukan RM); `max_tokens` default 5000.
4. **`42566ee` W4+W6 prompt** — **punca 'draf tak lengkap'**: prompt hanya bawa subset minimum. Kini `minimizedData` v2 (sejarah/visi-misi/perutusan/khidmat/kelas/kuliah/FAQ/seed/program penuh, PII-min kekal); `requestedKeys` +visi_misi/perutusan/faq, `announcements` bergerbang seed; `PiiScrubber` + blok NOTA & CITARASA PIC (step-7 rujukan + step-9 free_notes + nota PIC); system prompt +peraturan 7&8.
5. **`3a16bbf` W6 shell** — `DraftRenderer` verbatim (perutusan nama/AJK cap12/bank/hubungi dari wizard — render LOKAL, bukan AI) + hero data-URI (upload ≤1.5MB); `shell.blade` seksyen Perutusan/Visi-Misi/AJK/FAQ/bank/hubungi.
6. **`17927f7` W2 admin** — `ViewProject` + `ProjectInfolist` (9 Section: SEMUA data wizard/aset/draf-kos/nota via `ProjectDataPresenter` Markdown); balas nota (+WA PIC, event `note.admin_replied`); `AdminFileController` route `admin.aset`/`admin.draf`.
7. **`ca674c1` W3 brief** — `BriefBuilder` + `resources/brief/full-brief.blade`: brief MD LENGKAP (ARAHAN AI PEMBINA + org penuh + kandungan verbatim + bank + nota + QA) muat turun dari admin (submitted+); Semak PIC papar nilai (bank bermask).

**Keputusan direkod:** pengayaan prompt kedua-dua tier · kos papar USD sahaja · balasan nota admin → WA PIC · validator NGO perenggan kekal 1000 (skema panduan ≤600). **Tiada migration baharu.**

### Pembetulan pasca-Fasa 11 (audit, 10 Jul 2026)

1. **`eca8f80` fix(ngo):** render wizard **langkah 3/4** ikut tier — `step-3`/`step-4.blade` dulu guna `PageCatalog::meta()/panels()/clusters()` (masjid sahaja) → render NGO **500 "Undefined array key derma"**. Kini `WizardStep::render()` pass `metaFor/clustersFor/panelsFor($tier)`; blade guna `$pageMeta/$pageClusters/$pagePanels`. +2 ujian regresi HTTP.
2. **`d027778` fix(dev+admin)** — ditemui semasa **audit visual Chrome hujung-ke-hujung**:
   - **Vite dev `[::1]`:** `vite.config.js` set `server.host: '127.0.0.1'`. Tanpa ini Vite bind IPv6 & tulis `http://[::1]:5173` ke `public/hot` → `[::1]` bukan host-source CSP sah → **semua aset dev disekat, halaman awam runtuh tanpa gaya** bila `npm run dev`.
   - **Tetapan berselerak:** `ManageSettings` guna kelas Tailwind arbitrari dalam blade (tak dikompil tema panel Filament). Tukar ke **Filament Schema** (`Section`+`TextInput`).

---

## Fasa 11 — 11 commit (Julai 2026)

Semua ujian hijau selepas setiap commit. Laluan **masjid kekal byte-identik** sepanjang.

1. **fix(admin+wizard)** — `ProjectStatus`/`Tier` implement Filament `HasLabel`(+`HasColor`) → betulkan **500 `/admin/projects`** (dulu closure `fn (ProjectStatus $s)` cuba instantiate enum). `WizardStep::next()` langkah terakhir → `pic.semak` (butang "Seterusnya" langkah 9 tak lagi mati). Un-orphan tetapan (LeadsTable/DesignRerenderService baca Settings).
2. **feat(wizard) autosave skipRender** — betulkan **dropdown L4 tertutup sendiri**: `updated()` `skipRender()` untuk simpanan skalar/select; hanya radio/checkbox (pemacu showIf) & langkah reaktif render. Chip "Disimpan" via event Alpine (`wizard-saved`) + `wire:key` per medan.
3. **feat(design) FontPairs + palet custom** — `app/Support/FontPairs.php` (10 pasangan A–J, satu sumber); **18 font @fontsource di-hos-sendiri** (B/C/D dulu hilang → jatuh serif; kini pratonton bertindak balas). Fix `design-preview` guna font body. `app/Support/PaletteDeriver.php` — mod "Pilih sendiri" (HSL + gelap-auto WCAG ≥ 4.5:1).
4. **feat(design) 14 pakej + varian** — kolum `design_packages.variants` (header/footer/card/divider); seeder 5→**14 pakej** (semua token disahkan WCAG); `Moods` 3→5; container 4→6 (+kotak-tegas/heksagon); layout 4→6 (+hero-penuh/hero-mihrab); mini-mockup pratonton bertindak balas layout + nada.
5. **feat(draft) varian shell** — punca "semua laman sama": `shell.blade.php` kini bercabang `layout/header/footer/card/divider/animasi/ikon` (allowlist `DesignResolver` → tak boleh pecah). Default = rupa produksi. `<body data-layout data-header>`.
6. **feat(wizard) hero berbilang** — step-6 `multiple` + thumbnail + buang + maks 3; route bertoken `/b/{token}/aset/{asset}`.
7. **feat(ngo) tier + wizard** — migration `projects.tier` enum→string(40); `Tier` +2 case (isNgo/isMosque/orgNoun/values); `PageCatalog::clustersFor/metaFor/panelsFor($tier)`; panel NGO (profil/program/sukarelawan/keahlian/derma) + jawatan ROS; PresetMatrix NGO; step-0 5 kad 2 kumpulan; minat `org_type`; CompletenessService kecuali zon NGO.
8. **feat(ngo) AI + shell + spec** — `draft-system-ngo.txt`; PromptBuilder requestedKeys/schemaFor/minimizedNgoData (PII-min); Validator +4 kunci; shell seksyen NGO; `showPrayer = tier->isMosque()`; SpecBuilder content NGO.
9. **feat(notify) wehdah** — `WhatsappGateway` → `POST {base}/v1/messages/send` + `X-API-Key` + `{to,message,session_id?}`; kunci Settings baharu (api_key encrypted/session_id/admin_notify_phone); 3 event WA→admin (lead/submitted/nota) + AdminAlertMail; ManageSettings 3 seksyen + "Uji Hantar".
10. **feat(flow) auto-jana + harga** — `SemakController::submit()` auto-jana draf (GateException → amaran mesra); buang soalan bajet step-9; harga RM3,000 + RM1,000/thn (landing FAQ + step-8/9).
11. **test(e2e)+docs** — `reka:demo-token --ngo`; smoke Playwright +5 skrin NGO; HANDOVER/README.

## Preset penyedia AI (Julai 2026)

Admin pilih vendor → base URL + driver auto → API key + model. OpenAI/Anthropic/OpenRouter/DeepSeek/GLM·Z.ai/Groq/Mistral/Gemini/Ollama/Custom (`app/Enums/AiVendor.php`).

## Ujian smoke (Playwright) — `tests-e2e/`

`npm run test:e2e` — 29 halaman (24 masjid + 5 NGO) × 3 saiz skrin. `reka:demo-token` (+`--ngo`) jana sesi demo. **Alat dev sahaja.**

---

## Nota penting

1. **git/gh:** guna `env -u GH_TOKEN -u GITHUB_TOKEN git push / gh ...` (token harness tak sah).
2. **CSP + Vite dev:** `SecurityHeaders.php` gate `local`-sahaja (`withViteDevHosts()`). Production/testing kekal ketat. Gotcha: IPv6 `[::1]` BUKAN host-source sah — guna `localhost`/`127.0.0.1`.
3. **Font pratonton:** semua pasangan A–J di-hos-sendiri (@fontsource) — WAJIB kerana CSP awam `font-src 'self'`. Shell draf kekal Google Fonts (konteks berbeza).
4. **Varian design:** semua nilai varian divalidasi allowlist di `DesignResolver` — nilai tak dikenali fallback default, render tak boleh pecah.

## Tindakan tertunggak sebelum go-live (bukan bug)

- **Deploy: WAJIB `php artisan migrate`** selepas `git pull` (Fasa 14 — migration `is_prompt_engineer` mesti dijalankan; kalau tertinggal, edit Penyedia AI akan 500 "no such column"). Seeder kini idempoten (`putIfMissing`) — `db:seed --class=SettingsSeeder` selamat diulang, tidak menindih kunci API admin.
- **Salin Prompt (Fasa 14) perlu HTTPS** di produksi — `navigator.clipboard` hanya berfungsi konteks selamat (localhost dev OK). Tanpa HTTPS, butang senyap (tiada ralat).
- **Saluran HTML (Fasa 13) — konfigur 2 penyedia AI** di admin **Penyedia AI**: (1) OpenAI `gpt-5.5` + toggle **Jurutera Prompt**; (2) OpenRouter `z-ai/glm-5.2` + toggle **Default** (cadang `timeout_s`=180 kerana output HTML besar). Kunci API tampal via borang (encrypted) — **JANGAN commit**. Tetapan **Saluran draf** = `HTML` (sudah lalai seed). Tanpa penyedia Jurutera Prompt, penjanaan **gagal terus** (mail+WA admin).
- **Kunci API WhatsApp** (`whatsapp_api_key`) — tampal melalui borang **Tetapan admin** (encrypted DB). **JANGAN commit.** Kemudian tekan "Uji Hantar" (mesej sampai 60189030363 dari peranti 60174627287).
- **Migration tier→string** (`2026_07_11_000002`) — sudah lulus SQLite dev; **jalankan `php artisan migrate --pretend` di staging MySQL** sebelum deploy produksi (sahkan `MODIFY COLUMN VARCHAR(40)` kekalkan nilai).
- `verse_library` seed = `PENDING_MANUAL_ENTRY` — **Azan WAJIB** isi teks Arab sebenar Surah At-Taubah:18 (R6 §9.2). Jangan taip dari ingatan.
- `php artisan zones:verify` di produksi (59 kod zon JAKIM).
- Notis privasi/terma dwibahasa & `docs/SOP-PELANGGARAN-DATA.md` = draf — perlu semakan perundangan.
- **Kaveat draf NGO/hero:** imej hero dalam draf sampel guna placeholder warna (bukan imej sebenar) — imej sebenar untuk laman produksi.

## Perintah penting

```bash
php artisan test                 # 260 ujian Pest
php artisan migrate:fresh --seed # skema + seed (59 zon, 14 pakej, verse, 9 settings)
npm run build                    # aset (guna ini untuk ujian browser tempatan)
vendor/bin/pint --dirty          # format PHP
php artisan reka:demo-token --ngo # jana sesi NGO demo (dev sahaja)
php artisan zones:verify         # sahkan zon JAKIM (WAJIB sebelum prospek pertama)
```

Login admin dev: `admin@reka.test` / `password` (2FA app authenticator dipaksa).

Rujuk juga: `README.md`, `docs/GO-LIVE-CHECKLIST.md`, `docs/QA-RUN-F10.md`, `docs/SPEK-REKA-v1.1.md`.
