# HANDOVER — REKA (Website Builder)

Kemas kini terakhir: **11 Julai 2026** · Branch: `main` · Remote: `github.com/hakimalek27/Website-Builder`

REKA — platform tempahan & penjanaan draf laman web **masjid, surau & NGO/pertubuhan Islam**.
Stack: **Laravel 13.19 · PHP 8.4 · Filament v4.11 · Livewire 3 · Tailwind 4 · Pest** (dev: SQLite).

---

## Status semasa

- **Fasa 0–10** + **rombakan UI/UX** + **Fasa 11** + **pembetulan pasca-audit** (`eca8f80`, `d027778`) + **fix AiClient OpenAI moden** (`f61ddec`) + **Fasa 12** (7 commit: `806d17a`→`ca674c1`).
- **180 ujian Pest hijau** (648 assertions) · `pint` bersih · `npm run build` bersih.
- Semua kerja **di-push ke `main`**.

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

- **Kunci API WhatsApp** (`whatsapp_api_key`) — tampal melalui borang **Tetapan admin** (encrypted DB). **JANGAN commit.** Kemudian tekan "Uji Hantar" (mesej sampai 60189030363 dari peranti 60174627287).
- **Migration tier→string** (`2026_07_11_000002`) — sudah lulus SQLite dev; **jalankan `php artisan migrate --pretend` di staging MySQL** sebelum deploy produksi (sahkan `MODIFY COLUMN VARCHAR(40)` kekalkan nilai).
- `verse_library` seed = `PENDING_MANUAL_ENTRY` — **Azan WAJIB** isi teks Arab sebenar Surah At-Taubah:18 (R6 §9.2). Jangan taip dari ingatan.
- `php artisan zones:verify` di produksi (59 kod zon JAKIM).
- Notis privasi/terma dwibahasa & `docs/SOP-PELANGGARAN-DATA.md` = draf — perlu semakan perundangan.
- **Kaveat draf NGO/hero:** imej hero dalam draf sampel guna placeholder warna (bukan imej sebenar) — imej sebenar untuk laman produksi.

## Perintah penting

```bash
php artisan test                 # 144 ujian Pest
php artisan migrate:fresh --seed # skema + seed (59 zon, 14 pakej, verse, 9 settings)
npm run build                    # aset (guna ini untuk ujian browser tempatan)
vendor/bin/pint --dirty          # format PHP
php artisan reka:demo-token --ngo # jana sesi NGO demo (dev sahaja)
php artisan zones:verify         # sahkan zon JAKIM (WAJIB sebelum prospek pertama)
```

Login admin dev: `admin@reka.test` / `password` (2FA app authenticator dipaksa).

Rujuk juga: `README.md`, `docs/GO-LIVE-CHECKLIST.md`, `docs/QA-RUN-F10.md`, `docs/SPEK-REKA-v1.1.md`.
