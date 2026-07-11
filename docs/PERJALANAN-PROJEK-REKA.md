# Perjalanan Projek REKA — dari Awal hingga Demo PERKIB

> Catatan lengkap perjalanan projek **REKA** (platform tempahan & penjanaan draf laman web masjid, surau & NGO/pertubuhan Islam milik **Wehdah Solution**) — dari pembinaan awal, audit visual, sehingga demo hujung-ke-hujung mengisi wizard sebagai PIC untuk **PERKIB**.
>
> Kemas kini terakhir: **11 Julai 2026** (Fasa 14) · Branch `main` · Remote `github.com/hakimalek27/Website-Builder`
> Stack: Laravel 13.19 · PHP 8.4 · Filament v4.11 · Livewire 3 · Tailwind 4 · Pest · Intervention Image v4 (dev: SQLite)

---

## BAHAGIAN A — Perjalanan Pembinaan REKA

Dibina mengikut `docs/SPEK-REKA-v1.1.md`. Aliran teras: **Lead (minat) → Admin layakkan → Wizard PIC 10 langkah → Hantar → Jana draf AI → Semak/Tweak → Lulus → Eksport ZIP**.

### Fasa 0–10 — Teras sistem (`a89d6a0` → `b1e3b92`)
| Fasa | Kandungan |
|---|---|
| 0–1 | Skema (users PK BIGINT auth, 20 jadual domain ULID), seed 59 zon JAKIM, 5 pakej design, verse library, settings |
| 2 | Borang Minat awam + LeadQualifier (lead → projek + token PIC) |
| 3 | Wizard langkah 0–3 (tier, maklumat asas, reka bentuk, struktur halaman) |
| 4 | Wizard langkah 4 (enjin panel kandungan) + autosave |
| 5–6 | UploadService (finfo MIME, re-encode Intervention v4, buang EXIF GPS, resize), CompletenessService, gate Hantar |
| 7 | **AiClient** (Anthropic + OpenAI-compatible + fallback), PromptBuilder (PII-min §12.7), DraftContentValidator (§8.4), GenerateDraftJob (7 langkah §8.6), DraftRenderer + shell (watermark/noindex) |
| 8 | Pemapar draf (P5 iframe sandbox), P6 CSP ketat + noindex, tweak reka/kandungan, kelulusan (snapshot beku), HandoverExporter ZIP (§14.1) |
| 9 | WhatsappGateway + SendWhatsappJob (fallback mail), Notifier (9 event §13), reminders |
| 10 | Audit E2E, docs QA, GO-LIVE checklist |

### Rombakan UI/UX "Premium Islamik-Moden" (`2fed894`, 9 Jul)
Sistem reka baharu `resources/css/app.css`: token `@theme` Tailwind 4 (ramp brand zamrud + gold emas + cream/sand/ink), komponen `.btn/.card/.input`, corak `.bg-pattern-islamic` (Rub el Hizb SVG), animasi + reveal-on-scroll. Font **di-hos-sendiri** (@fontsource — WAJIB kerana CSP `font-src 'self'`). Komponen Blade guna-semula `components/ui/*`. Landing 8 seksyen. Admin Filament brandLogo + `Color::hex('#1B5E3F')`.

### Preset penyedia AI (`a964fb7`, 10 Jul)
`app/Enums/AiVendor.php` — admin pilih vendor (OpenAI/Anthropic/OpenRouter/DeepSeek/GLM·Z.ai/Groq/Mistral/Gemini/Ollama/Custom) → base URL + driver auto-isi + dropdown model. Selesaikan ralat 404. Borang `AiProviderForm` reaktif.

### Fasa 11 — Pepijat + NGO + pelbagaian design + auto-jana + WhatsApp Wehdah (`5bd4f28` → `0bfde79`, 11 commit)
Laluan masjid kekal **byte-identik** sepanjang.
- **Pepijat:** (1) `ProjectStatus`/`Tier` implement Filament `HasLabel`+`HasColor` → betulkan 500 `/admin/projects`. (2) dropdown L4 tertutup sendiri → `skipRender()` autosave. (3) font pratonton → FontPairs A–J self-hosted. (4) butang L9 mati → `next()`→semak. (5) buang bajet L9.
- **NGO penuh:** `projects.tier` enum→string(40); `Tier` +NgoKomuniti/NgoPenuh (isNgo/isMosque/orgNoun); `PageCatalog::clustersFor/metaFor/panelsFor($tier)`; panel NGO (profil/perutusan[ROS]/visi_misi/ajk/program/sukarelawan/keahlian/derma); PromptBuilder/Validator/shell/SpecBuilder cabang NGO (`showPrayer=tier->isMosque()`, PII-min).
- **Pelbagaian design:** 5→**14 pakej** WCAG · 3→5 mood · 6 container · 6 layout · **shell bercabang varian** (header/footer/kad/pembatas/animasi) · PaletteDeriver custom · hero berbilang (maks 3).
- **Auto-jana:** `SemakController::submit()` auto-panggil `DraftGenerationService::request()`. Harga RM3,000 + RM1,000/thn.
- **WhatsApp Wehdah:** `WhatsappGateway` → `POST {base}/v1/messages/send` + `X-API-Key`; kunci Settings (encrypted); 3 event WA→admin + AdminAlertMail; ManageSettings 3 seksyen + "Uji Hantar".

**Status: 146 ujian Pest hijau (535 assertions).**

---

## BAHAGIAN B — Audit Visual Chrome (10 Jul 2026)

Lalui **setiap halaman** guna Claude-in-Chrome (awam sebelum-login → admin dalam). **2 bug sebenar ditemui & dibaiki:**

### Bug 1 — Semua halaman awam runtuh tanpa gaya bila `npm run dev` (`d027778`)
- **Punca:** Vite bind IPv6 loopback → tulis `http://[::1]:5173` ke `public/hot`. `[::1]` **BUKAN host-source CSP yang sah** → gate `withViteDevHosts` (localhost:*/127.0.0.1:*) tak padan → browser sekat SEMUA aset dev (CSS/JS).
- **Fix:** `vite.config.js` `server.host: '127.0.0.1'`. Diagnosis pantas: `cat public/hot` — kalau `[::1]` itu puncanya.

### Bug 2 — Halaman Tetapan (ManageSettings) berselerak (`d027778`)
- **Punca:** borang HTML mentah guna kelas Tailwind arbitrari (`grid-cols-2`/`space-y-8`/`border-gray-300`) yang **TIDAK dikompil tema panel Filament** (hanya kelas yang Filament sendiri guna wujud dalam `/css/filament/filament/app.css`) → medan bertindih.
- **Fix:** tulis semula guna **Filament Schema** (`Section`+`TextInput`, `->columns()`, `{{ $this->form }}`).
- **Pelajaran:** halaman Filament custom JANGAN guna HTML mentah + Tailwind arbitrari — guna komponen Filament.

### Bug 3 (sebelum audit) — Render wizard NGO langkah 3/4 → 500 (`eca8f80`)
`step-3`/`step-4.blade` guna `PageCatalog::meta()/panels()/clusters()` (masjid sahaja) → NGO 500 "Undefined array key derma". Fix: `WizardStep::render()` pass `metaFor/clustersFor/panelsFor($tier)`. +2 ujian regresi HTTP.

Semua halaman lain (landing, minat, privasi, terma, dashboard, Lead, Projek, Penyedia AI, Jemputan) render kemas. Badge Tier NGO + org_type berfungsi.

---

## BAHAGIAN C — Demo E2E: Isi Wizard sebagai PIC PERKIB (Chrome)

**Objektif:** Bertindak sebagai PIC untuk NGO **Pertubuhan Kebajikan Imam dan Bilal MAIWP (PERKIB)**, isi wizard REKA hujung-ke-hujung sehingga draf laman dijana.

### C.1 Kajian dokumen (sebelum sentuh Chrome)
- **Perlembagaan PDF** (extract via Python `pypdf` — Read tool gagal sebab tiada poppler): nama rasmi, **No. ROS PPM-013-14-08022021**, alamat (Blok F1, Taman Melati Kawasan 8, 53100 Wangsa Maju KL), Visi, Misi (5), objektif, bidang tugas.
- **Carta organisasi 2025/2026**: 24 AJK (8 Majlis Tertinggi + 8 Perwakilan Zon + 8 AJK Kluster) — nama + jawatan.
- **Folder `C:\MAIWP_Imam_Bilal_2026-07-07`**: ~90 gambar AJK + CSV rekod (91 pegawai: 31 Ketua Imam, 32 Timbalan, 28 Bilal). **Kolum masjid/zon KOSONG 91/91** (disahkan CSV + Excel backup).
- **PDPA:** CSV ada No. KP + telefon peribadi ~90 imam/bilal — **TIDAK** dimasukkan ke laman awam.

### C.2 Gap analysis + keputusan pengguna
Data cukup: nama, alamat, ROS, visi/misi, AJK 24, logo, profil. Perlu keputusan:
- **Derma:** YA — Bank Rakyat **11-0175647-7** (Pertubuhan Kebajikan Imam Bilal MAIWP) + DuitNow QR.
- **Gambar AJK:** semua 24 + halaman baru direktori.
- **Program:** bina dari bidang tugas perlembagaan.
- **Foto hero/galeri:** stok sementara.
- **Hubungi:** telefon utama placeholder, telefon PIC 0189030363, emel azanmalek@maiwp.gov.my.

### C.3 Aliran sebenar dilalui
1. **Borang Minat** (`/minat`) — isi sebagai PERKIB (NGO) → Lead. *(Perasan medan honeypot `website_url` tersembunyi — dikosongkan elak ditolak sebagai bot.)*
2. **Admin** — "Layakkan & Jemput" → Projek + jemputan; "Buka sebagai PIC" → jana token wizard.
3. **Wizard 0–9** (semua diisi via API Livewire `Livewire.find(id).set(...)`):
   - L0 tier `ngo_komuniti` · L1 identiti · L2 **Amanah Biru** (biru korporat) + nada Megah & Berwibawa + Manrope/Marcellus + klasik-formal + pengaki tiga-lajur · L3 12 halaman + custom "Direktori Masjid WP" · L4 kandungan penuh 10 panel (profil, visi/misi, perutusan, **24 AJK**, 4 program, sukarelawan, keahlian, **Derma bank**, FAQ, hubungi) · L5 CMS selenggara · L6 hero stok · L7 panduan AI · L8 domain belum · L9 PIC + perakuan.
4. **Semak** → 100% (30/30) → **Hantar Maklumat** → status Dihantar.
5. **Jana** → GLM-4.6 (dikonfig pengguna) → **draf siap dalam 16 saat** → status Draf Sedia.

### C.4 Isu ditemui & diselesaikan sepanjang aliran
1. **`file_upload` MCP tak terima path cakera** ("no longer accepts host filesystem paths") → logo, 24 gambar AJK, QR **tak dapat diautomasi**. Penyelesaian: logo jadi **gaya teks**, AJK **nama+jawatan sahaja**; gambar perlu upload manual/CMS.
2. **`logo_status` tersimpan ke section salah** — set semasa di L4 (component L4) → simpan ke section `step_4`, bukan `step_1`. `canGenerate` baca `step_1.logo_status` = 'ada' (nilai L1 asal) → GateException "Logo/hero belum lengkap". Fix: betulkan section `step_1.logo_status='teks_sahaja'`.
3. **Perutusan: array vs objek** — di-set sebagai array `[{...}]` tapi CompletenessService cari objek tunggal `panels.perutusan.role` → 90% (3 medan "kosong"). Fix: set sebagai objek → 100%.
4. **Job pada queue `ai`** (bukan `default`) — GenerateDraftJob `onQueue('ai')`. Worker `--queue=default` tak ambil. Fix: `queue:work --queue=ai`. (Nota: dev QUEUE=database, perlu worker; MAIL=log.)

### C.5 Hasil — Draf laman PERKIB
Draf penuh dijana dengan kandungan GLM-4.6 berkualiti + tema Amanah Biru:
- Header biru korporat + nav (Perutusan, Visi & Misi, AJK, FAQ, Hubungi)
- Hero "Bersama Membangun Ummah" + CTA Daftar Ahli / Terima Derma + pembatas emas
- Mengenai PERKIB (ditubuhkan 2021, KL), Program 4 kad (Dakwah/Kebajikan/Sosial/Ekonomi), Sukarelawan, Keahlian
- Grid 12 halaman (termasuk Direktori Masjid WP), footer tiga-lajur

URL draf penuh: `/b/{token}/draf/{generation}/penuh`

---

## BAHAGIAN D — Kekangan & Tindakan Lanjut (untuk laman produksi)

- **Muat naik manual:** logo PERKIB, 24 gambar AJK, QR DuitNow — pengguna klik "Choose File" sendiri (had `file_upload` MCP), atau via CMS produksi.
- **Telefon utama:** kini placeholder `03-0000 0000` — ganti talian sebenar.
- **Direktori Masjid WP:** senarai masjid penuh + carian ikut zon = fasa CMS (data masjid tiada dalam fail yang diberi — kolum kosong).
- **Kunci API WhatsApp:** tampal via Tetapan admin (encrypted) + "Uji Hantar".
- **`verse_library`:** Azan isi teks Arab sebenar At-Taubah:18 (jangan taip dari ingatan).
- **Migrasi `tier→string`:** `php artisan migrate --pretend` di staging MySQL sebelum deploy.

---

## BAHAGIAN E — Fasa 12: Visibiliti, Brief, Nota→AI, Kos Model & Pengayaan Prompt (11 Jul 2026)

Selepas demo PERKIB, product owner laporkan **6 aduan**. Diselesaikan dalam **7 commit** (`806d17a`→`ca674c1`), **146→180 ujian**. Pelan penuh: `~/.claude/plans/kemaskini-ui-ux-setiap-cozy-muffin.md`.

### Kaedah — 3 agen Explore + 1 agen Plan (selari)
Aduan owner disiasat dengan 3 agen Explore serentak (navigasi PIC · admin/kos/brief · **audit mendalam saluran prompt**), kemudian 1 agen Plan reka pelaksanaan. **Penemuan teras audit:** data wizard mengalir ke **3 sink berasingan** — (A) prompt AI = subset minimum, (B) render draf = output AI + blok statik, (C) spec.json/handover = SEMUA data tapi hanya selepas kelulusan. Langkah 7 (rujukan), free_notes L9, visi_misi, perutusan, AJK, FAQ, seed berita **tidak pernah sampai ke AI**; `announcements`/`membership` diminta **tanpa data** → AI paksa tulis ayat generik. Inilah punca sebenar "draf tak lengkap & tak menarik".

### 7 commit
| Commit | Bidang | Ringkasan |
|---|---|---|
| `806d17a` | **W1 nav PIC** | Bar nav status-aware (`components/pic/nav`) di semua halaman PIC; `Project::latestDraft()`; baris "Draf terdahulu" JanaHub jadi pautan; kad pintasan home. (Aduan: Jana Draf tak boleh ditemui.) |
| `578fd60` | **W1 deep-link WA** | `DraftGenerationService::request(picBaseUrl)` → `GenerateDraftJob` bina pautan draf sebenar dalam WA (bukan string mati); job `ShouldBeEncrypted` (payload bawa token). |
| `6a3908c` | **W5 kos** | `app/Support/ModelRates.php` (harga rasmi USD/MTok: gpt-5.5 5/30, opus-4-8 5/25, glm-5.2 1.40/4.40 + lain, di-fetch WebSearch); `AiProviderForm` auto-isi kadar; label kos **USD** (betulkan RM tanpa penukaran); `max_tokens` 5000. |
| `42566ee` | **W4+W6 prompt** | `PromptBuilder::minimizedData` v2 perkaya (sejarah/visi-misi/perutusan/khidmat/kelas/kuliah/FAQ/seed/program penuh, PII-min kekal); `requestedKeys` +visi_misi/perutusan/faq, `announcements` bergerbang seed; `PiiScrubber` + blok NOTA & CITARASA PIC; system prompt +peraturan 7&8. |
| `3a16bbf` | **W6 shell** | `DraftRenderer` verbatim (perutusan nama/AJK cap12/bank/hubungi = render LOKAL, BUKAN ke AI) + hero data-URI (upload ≤1.5MB); `shell.blade` seksyen Perutusan/Visi-Misi/AJK/FAQ/bank/hubungi. |
| `17927f7` | **W2 admin** | `ViewProject`+`ProjectInfolist` (9 Section — SEMUA data wizard/aset/draf-kos/nota via `ProjectDataPresenter` Markdown); balas nota (+WA PIC, event `note.admin_replied`=13); `AdminFileController` route `admin.aset`/`admin.draf`. |
| `ca674c1` | **W3 brief** | `BriefBuilder`+`resources/brief/full-brief.blade` = brief MD LENGKAP (ARAHAN AI PEMBINA + org penuh + kandungan verbatim + bank + nota + QA) muat turun admin (submitted+); Semak PIC papar nilai (bank bermask). |

### Keputusan owner direkod
Pengayaan prompt **kedua-dua tier** · kos papar **USD sahaja** (tiada penukaran RM) · balasan nota admin → **WhatsApp PIC** · validator NGO perenggan kekal **1000** (skema panduan ≤600) · **TIADA migration baharu**. PII-min §12.7 kekal (bank/telefon/PIC **tak** ke AI; bank dirender LOKAL dalam draf sahaja).

### Nota
Setiap commit gate `pint` + `php artisan test` penuh (+`npm run build` bila blade). Walkthrough Chrome manual (langkah verifikasi pelan) **tidak** dibuat — alat interaktif extension Chrome bermasalah sepanjang sesi; liputan penuh oleh 180 ujian automasi (8 fail ujian baharu Fasa 12: PicNav/DeepLink/ModelRates/PromptEnrichment/NotesInPrompt/ShellEnrichment/ViewProject/BriefBuilder).

---

## BAHAGIAN F — Fasa 13: Saluran Draf HTML Dua-Peringkat (12 Jul 2026)

Owner mahu draf yang lebih **cantik & telus**: bukan lagi JSON→templat tetap, tetapi **HTML sebenar** yang PIC boleh klik-klik. Aliran dua-peringkat:

> **PIC hantar → GPT-5.5 (Jurutera Prompt) susun prompt lengkap → GLM-5.2 (Default) jana draf HTML statik → PIC lihat/klik/tweak → admin nampak SEMUA (prompt, kod, kos, tweak).**

### Kaedah — 3 agen Explore + 1 agen Plan (selari)
Saluran sedia ada dipetakan dengan 3 agen Explore serentak (saluran jana penuh · penyedia/tetapan/admin · prompt/design/validator), kemudian 1 agen Plan reka pelaksanaan. **Penemuan teras:** saluran lama mengandaikan **output JSON** (`DraftContentValidator` + Blade `shell`), jadi HTML statik perlu **laluan validasi + render + CSP tersendiri** — bukan sekadar tukar prompt.

### 7 commit (`22b159a`→`0a5a172`, 226 ujian)
| Commit | Bidang | Ringkasan |
|---|---|---|
| `22b159a` | **W1 asas** | `AiClient::complete($options)` (json/max_tokens); `is_prompt_engineer` (satu, `promptEngineer()`); Setting `draft_pipeline`(shell/html)+`html_max_tokens`; `progress_steps_html`; ModelRates `z-ai/glm-5.2`; `DB_QUEUE_RETRY_AFTER=360`. |
| `47a53d9` | **W2 prompt** | `prompt-engineer-system.txt`+`html-draft-system.txt`; `HtmlPromptBuilder` (KONTEKS PII-min + reka bentuk hex/fon/varian + halaman + PLACEHOLDER + nota); `PromptBuilder::minimizedContext()`; `DraftRenderer::verbatimFor/heroImageFor`. |
| `f874ddd` | **W3 validasi/finisher** | `HtmlDraftValidator` (ekstrak doctype→</html>, tolak Arab/`<script>`/URL-luar/>400KB); `HtmlDraftFinisher` (ganti `[[...]]` verbatim + noindex/banner/watermark/"— DRAF"); 5 partial. |
| `dfd3126` | **W4 job** | `GenerateDraftJob` cabang `handleShell`/`handleHtml` (P1→P2, retry HANYA P2 jimat token); `resolvePipeline`/`pipelineMode`; `Notifier` WA-gagal admin. |
| `f25b6f2` | **W5 PIC UX** | tweak HTML (guna HTML **mentah bertoken**, bukan draf PII → §12.7); guard tweak-reka; JanaHub label saluran + baki + banner gagal; submit → `pic.jana` (progres langsung). |
| `87f8145` | **W6 admin** | `AdminFileController::prompt/draftDownload`; ProjectInfolist per-gen html (sumber/kos P1-P2/prompt/tweak); Brief "Prompt Jurutera" + "Thread Tweak". |
| `0a5a172` | **W7 pratonton** | design-preview varian header/footer/pembatas + fix `arabic_font` tak disalin ke overrides. |

### Keputusan owner direkod
Mod **boleh-tukar** (saluran lama kekal) · had tweak = **kuota sedia ada** (3 = 1 jana + 2 tweak) · jurutera prompt gagal/tak diset → **GAGAL TERUS** (mail+WA admin, kuota tak dicaj) · **PII-min §12.7 kedua-dua peringkat** (bank/telefon/nama TIDAK ke AI — masuk HTML melalui **placeholder `[[...]]`** diganti server; tweak hantar HTML **mentah bertoken**) · **TIADA migration enum** (pipeline dalam `input_snapshot`) · anggaran **~USD 0.44/jana penuh**.

### Nota teknikal Fasa 13
1. **Dua provider berperanan:** `is_default` (P2 jana HTML) + `is_prompt_engineer` (P1 jana prompt) — kedua dikuatkuasa "satu sahaja" via `AiProvider::booted()`.
2. **CSP `raw` draf kekal** (`default-src 'none'`; style-src Google Fonts; img-src self+data:) → HTML tanpa JS + fon Google berfungsi; `sandbox=""` anchor-nav masih jalan.
3. **Retry pintar:** engineered prompt disimpan sebaik P1 siap; kegagalan validasi ulang **HANYA P2** (elak bazir token GPT-5.5). Kos = jumlah 2 peringkat; gagal muktamad rekod kos terbazir.
4. **HTML mentah bertoken** disimpan (`{gen}.raw.html`) selain draf siap (`{gen}.html`) — supaya tweak hantar versi TANPA PII ke GLM.
5. **Placeholder verbatim:** `[[CONTACT_STRIP]]`(wajib)·`[[BANK_BLOCK]]`·`[[AJK_GRID]]`·`[[PERUTUSAN_NAMA]]`·`[[HERO_IMAGE]]`·`[[WAKTU_SOLAT]]`·`[[AYAT_ARAB]]` (masjid). Token wajib hilang → fallback sebelum `</body>`; token yatim dibuang.
6. **Gotcha docblock:** urutan `*/` dalam komen (cth `on*/`) menutup blok komen awal → parse error. Elak.
7. **`file_upload`/Chrome interaktif** masih bermasalah — liputan penuh via **226 ujian automasi** (Phase13: AiClientOptions/PromptEngineerProvider/SettingsPipeline/HtmlPromptBuilder/HtmlValidator/HtmlFinisher/HtmlPipelineGeneration/HtmlTweak/PipelineUx/AdminHtmlVisibility/Step2Preview).

---

## BAHAGIAN G — Fasa 14: QA Auto, Salin Prompt, finish_reason, Varian Animasi & Audit Admin (11 Jul 2026)

Empat ciri dari senarai "cadangan masa depan" Fasa 13, ditambah **fix bug dilaporkan** (`# IlluminateDatabaseQueryException.txt` di Desktop) dan **audit dashboard admin menyeluruh** ("jgn terlepas apa2").

### Kaedah — 3 agen Explore + 1 agen Plan (selari)
Tiga agen Explore serentak (aliran AI + hook QA · admin Filament + inventori audit · design-preview animasi), kemudian 1 agen Plan. **Penemuan yang mengubah skop:**
- **Bug punca disahkan:** migration `2026_07_12_000001` (is_prompt_engineer) masih **Pending** pada DB dev — punca QueryException. Bug bonus: `SettingsSeeder` guna `put` → seed semula memadam kunci API WhatsApp.
- **"Varian animasi" tak wujud:** sistem sedia ada cuma **checkbox boolean** (satu efek fadeUp) dan pratonton **langsung tak menunjukkannya** — owner pilih **naik taraf 3 varian**.
- **Kontras `accent/bg` akan false-flag 100% draf** (pakej default `warisan_hijau` skor ≈2.1:1) → guna pasangan yang benar-benar dirender `primaryDark/accent`.

### 6 commit (`02c3e5c`→`9c534eb`, 258 ujian; +2 ModelRates → 260 keseluruhan)
| Commit | Bidang | Ringkasan |
|---|---|---|
| `02c3e5c` | **W1 fix bug** | `Setting::putIfMissing()` (guard `exists()`); `SettingsSeeder` idempoten (tidak tindih kunci API admin). Migration dijalankan → betulkan QueryException edit Penyedia AI. |
| `2dc1285` | **W2 finish_reason** | `AiResult::$finishReason` (normalkan `max_tokens`→`length`); `handleHtml` gagal awal bila P2 terpotong (jimat masa retry); rekod snapshot; ProjectInfolist `henti: length`. |
| `0af9ba9` | **W3 QA auto** | `DraftQaService` (seksyen `id={page_key}` + kontras token WCAG `primaryDark/accent` + inline lapor); wire ke Job **Throwable-safe** (tak halang draf) → `snapshot['qa']` + `Notifier::qaFlagged` (event `qa.flagged`); prompt diketatkan id deterministik. |
| `37c7822` | **W4 Salin Prompt** | `Action 'salinPrompt'` ViewProject → papan klip (`$livewire->js(navigator.clipboard)`) + notifikasi; visible bila prompt wujud. |
| `20ac88c` | **W5 varian animasi** | `tiada`/`fade`/`zoom`: `DesignResolver::ANIMATIONS`+`animationVariant()` (legasi bool→string); radio L2; pratonton `data-animation`+`<style>` berskop `rk-sec`+`wire:key`; shell `zoomIn`; prompt arahan CSS-only. TIADA migration. |
| `9c534eb` | **W6 audit admin** | buang `CreateAction` mengelirukan (ListProjects); `AdminAuditTest` boot-smoke semua permukaan + regresi simpan AiProvider. |

### Keputusan owner direkod
Animasi = **naik taraf 3 varian** (bukan sekadar pratonton toggle) · QA = **laporan + WA/mail admin bila ada isu** (draf TIDAK dihalang) · **TIADA migration** (varian animasi dalam overrides JSON; pipeline QA dalam `input_snapshot`).

### Nota teknikal Fasa 14
1. **Guard seeder `exists()`, bukan `get()`:** `whatsapp_session_id`/`api_key` memang di-seed **null** — `get()` tak boleh beza "baris null" vs "tiada baris"; hanya `exists()` betul.
2. **QA WAJIB Throwable-safe:** dipanggil dalam blok berjaya selepas fail draf disimpan; bug regex TIDAK boleh menggagalkan draf sah atau membakar kuota (try/catch + `report()`).
3. **finish_reason JANGAN bump max_tokens antara percubaan:** `OpenAiCompatibleClient::adaptPayload` sudah **turunkan** cap ("at most N") — bump akan berlawan; terpotong = isu konfigurasi `html_max_tokens`.
4. **Livewire morph replay animasi:** tanpa `wire:key` pada wrapper pratonton (keyed ikut varian), morphdom hanya patch atribut → animasi CSS **tak main semula** bila ditukar.
5. **`has-anim-fade` mengandungi substring `has-anim`:** ujian lama lulus **hampa** — kemas kini assertion semak kelas BODY tepat (definisi `.has-anim-*` sentiasa dalam stylesheet).
6. **Legasi boolean animasi 3 tempat:** overrides (resolver `animationVariant`), data step_2 → radio (`WizardStep::mount` normalize), paparan presenter (cabang `is_bool` kekal Ya/Tidak).
7. **Audit admin 2 lapis:** boot-smoke automasi (`AdminAuditTest` — semua resource/page/widget tanpa 500 + regresi bug sebenar) mengesahkan tiada 500; audit **visual** (2FA) diserah owner. Pratonton animasi W5 disahkan **langsung dalam browser** (radio → `data-animation="zoom"` → 4 `rk-sec` → `<style> rkZoomIn`).
8. **Salin Prompt perlu konteks selamat:** `navigator.clipboard` undefined atas HTTP bukan-localhost — guard `if (navigator.clipboard)` elak ralat; produksi WAJIB HTTPS.

### Kadar kos penuh + demo E2E tukar penyedia (11 Jul 2026, `9666542`)

Selepas Fasa 14 W6, jana PERKIB sebenar melalui saluran HTML → dedah 2 perkara + uji fleksibiliti penyedia:

**(a) Kadar kos setiap model** — `ModelRates` dulu hanya ada model unggulan; jana pertama papar **USD 0.00** kerana `cost()` baca kadar HANYA dari `provider->meta` (bukan fallback ModelRates) dan penyedia demo tiada kadar. Betulkan: rujuk laman rasmi vendor (Groq/Mistral/Google/xAI/Z.ai/OpenRouter) → isi kadar SEMUA model dropdown; ujian jamin setiap model auto-isi. Kini admin pilih model → kadar terisi → kos jana dikira betul.

**(b) Tukar penyedia disahkan** — owner minta uji GLM-5.2 (jurutera P1) + Gemini 2.5 Pro (penjana P2) menggantikan gpt-5.5 + glm-5.2. Routing disahkan: `AiProvider::promptEngineer()` → glm-5.2, `AiProvider::default()` → gemini-2.5-pro. Dua jana sebenar PERKIB:

| Config | P1 jurutera | P2 penjana | Kos |
|---|---|---|---|
| Asal | gpt-5.5 ($0.19) | glm-5.2 ($0.07) | ~USD 0.26 |
| Tukar | **glm-5.2 ($0.02)** | **gemini-2.5-pro ($0.13)** | **~USD 0.15** |

Kedua-dua: QA **LULUS**, 12 seksyen = tepat 12 halaman PIC pilih (prompt-ketat `id={page_key}` W3 berkesan), data verbatim (bank/AJK/hubungi) server-inject, prompt+pecahan kos+HTML dalam DB admin (`input_snapshot`) + brief muat turun. **Semua ikut apa yang PIC isi & pilih.**

**Pelajaran:** (1) penyedia Jurutera Prompt (P1) perlu `timeout_s` ≥ 180 bila model perlahan (GLM jana prompt > 90s → `cURL 28` → gagal-terus, kuota tak dicaj — gagal-terus berfungsi betul). (2) OpenRouter satu kunci melayani banyak model (GLM + Gemini kongsi kunci). (3) Config tukar lebih murah (~USD 0.15) kerana GLM jurutera jauh lebih murah dari gpt-5.5.

---

## Pelajaran teknikal utama (rujukan cepat)

1. **Vite dev + CSP:** IPv6 `[::1]` bukan host-source CSP sah → `vite.config` `server.host:'127.0.0.1'`.
2. **Halaman Filament custom:** guna Filament Schema, bukan HTML mentah + Tailwind arbitrari (tak dikompil tema panel).
3. **Isi borang Livewire via automasi:** guna `Livewire.find(wireId).set('data.x', v, false)` + `$commit()` — paling pasti; ambil `wireId` dari `[wire:id]`.
4. **Data wizard per-section:** medan disimpan ke section langkah **semasa** — set medan langkah lain dari component salah = simpan ke section salah (cth `logo_status`).
5. **Repeater vs objek tunggal:** semak struktur panel (cth `perutusan`=objek, `ajk.members`=array) sebelum set.
6. **Queue jana:** `GenerateDraftJob` pada queue **`ai`**; dev QUEUE=database perlu worker (`queue:work --queue=ai`).
7. **PDF extract:** Read tool perlu poppler; guna Python `pypdf` + tulis UTF-8 ke fail (elak `UnicodeEncodeError` cp1252 pada bullet ``).
8. **`file_upload` MCP:** tidak lagi terima path cakera — imej perlu dikongsi sesi / upload manual.
