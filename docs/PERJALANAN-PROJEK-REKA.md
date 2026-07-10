# Perjalanan Projek REKA — dari Awal hingga Demo PERKIB

> Catatan lengkap perjalanan projek **REKA** (platform tempahan & penjanaan draf laman web masjid, surau & NGO/pertubuhan Islam milik **Wehdah Solution**) — dari pembinaan awal, audit visual, sehingga demo hujung-ke-hujung mengisi wizard sebagai PIC untuk **PERKIB**.
>
> Kemas kini terakhir: **10 Julai 2026** · Branch `main` · Remote `github.com/hakimalek27/Website-Builder`
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

## Pelajaran teknikal utama (rujukan cepat)

1. **Vite dev + CSP:** IPv6 `[::1]` bukan host-source CSP sah → `vite.config` `server.host:'127.0.0.1'`.
2. **Halaman Filament custom:** guna Filament Schema, bukan HTML mentah + Tailwind arbitrari (tak dikompil tema panel).
3. **Isi borang Livewire via automasi:** guna `Livewire.find(wireId).set('data.x', v, false)` + `$commit()` — paling pasti; ambil `wireId` dari `[wire:id]`.
4. **Data wizard per-section:** medan disimpan ke section langkah **semasa** — set medan langkah lain dari component salah = simpan ke section salah (cth `logo_status`).
5. **Repeater vs objek tunggal:** semak struktur panel (cth `perutusan`=objek, `ajk.members`=array) sebelum set.
6. **Queue jana:** `GenerateDraftJob` pada queue **`ai`**; dev QUEUE=database perlu worker (`queue:work --queue=ai`).
7. **PDF extract:** Read tool perlu poppler; guna Python `pypdf` + tulis UTF-8 ke fail (elak `UnicodeEncodeError` cp1252 pada bullet ``).
8. **`file_upload` MCP:** tidak lagi terima path cakera — imej perlu dikongsi sesi / upload manual.
