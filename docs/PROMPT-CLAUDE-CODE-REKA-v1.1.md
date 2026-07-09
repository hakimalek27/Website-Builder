# PAKEJ PROMPT CLAUDE CODE — BINA SISTEM **REKA**
**Pasangan kepada:** `SPEK-REKA-v1.1.md` · **Versi:** 1.1 · 7 Julai 2026 (dikemaskini selaras semakan fakta v1.1 — perubahan pada Prompt 1, 6, 7, 9 sahaja; struktur fasa tidak berubah)
**11 prompt (Fasa 0–10).** Satu prompt = satu sesi kerja. Jangan gabung dua fasa dalam satu prompt.

---

## §A — CARA GUNA (Azan baca, bukan Claude Code)

1. Cipta folder projek kosong. Letak fail spek di dalamnya: `docs/SPEK-REKA-v1.1.md` ← **WAJIB, semua prompt merujuk fail ini.**
2. Buka Claude Code dalam folder itu. Paste **PROMPT 0** sepenuhnya. Tunggu siap + laporan tamat.
3. Semak laporan tamat (format seragam di bawah). Jika ada item merah/soalan → jawab dahulu sebelum sambung.
4. Paste PROMPT 1, dan seterusnya sehingga PROMPT 10. **Jangan langkau. Jangan paste prompt seterusnya selagi laporan fasa semasa belum bersih.**
5. Jika sesi Claude Code terputus/context habis: mulakan sesi baru dan paste semula prompt fasa yang sedang dibuat — BLOK PERATURAN dalam setiap prompt akan memulihkan konteks (dia akan baca semula spek + kod sedia ada).
6. Ujian AI guna `Http::fake()` — **tiada API key sebenar diperlukan sehingga deploy** (Fasa 10).

**Format laporan tamat (setiap fasa, Claude Code wajib keluarkan):**
```
LAPORAN FASA N
✅ Dibina: (senarai ringkas)
✅ Ujian fasa: X lulus / Ujian keseluruhan (regresi): Y lulus, 0 gagal
✅ Verifikasi: (output perintah §verifikasi)
⚠️ Isu/keputusan yang saya buat + rujukan seksyen spek: (atau "tiada")
❓ Soalan (jika ada — dan saya BERHENTI menunggu jawapan)
📁 Fail diubah: N fail
```

---

## §B — BLOK PERATURAN INDUK (dimasukkan penuh dalam PROMPT 0; ringkasannya diulang di kepala setiap prompt)

```
PERATURAN INDUK PROJEK REKA — TERPAKAI SETIAP FASA:

R1. SUMBER KEBENARAN TUNGGAL = docs/SPEK-REKA-v1.1.md. Setiap keputusan kod
    mesti boleh dirujuk kepada nombor seksyen spek. Jika anda membuat apa-apa
    keputusan kecil yang tidak dinyatakan spek, SENARAIKAN dalam laporan tamat
    dengan justifikasi.
R2. JANGAN TEKA. Jika spek kabur, bercanggah, atau mustahil dilaksanakan
    seperti ditulis → BERHENTI, terangkan percanggahan itu dengan nombor
    seksyen, cadangkan 2 pilihan, dan TANYA. Jangan teruskan atas andaian.
R3. SKOP FASA ADALAH PAGAR. Bina HANYA apa yang disenaraikan dalam prompt fasa
    semasa. Jangan bina awal ciri fasa hadapan "sementara tangan panas".
    Jangan sentuh §18 Backlog langsung.
R4. UJIAN BUKAN PILIHAN. Setiap fasa: tulis ujian Pest fasa itu → luluskan →
    kemudian jalankan SEMUA ujian terkumpul (regresi penuh). Fasa tidak tamat
    dengan mana-mana ujian gagal, walaupun "kegagalan kecil".
R5. JANGAN PADAM/UBAH ujian sedia ada untuk "membuatkannya lulus". Jika ujian
    lama gagal selepas kod baru, kod baru yang salah — atau bangkitkan di R2.
R6. GUARDRAIL AGAMA (§9) TIDAK BOLEH DIKOMPROMI: AI dilarang jana teks Arab
    (reject regex WAJIB wujud & diuji); waktu solat JAKIM sahaja; teks Arab
    seed disalin TEPAT dari sumber yang spek nyatakan — jangan taip dari
    ingatan anda.
R7. KESELAMATAN (§11) dibina PADA fasa berkenaan, bukan "nanti di hujung":
    rate limit, mask() logging, EXIF strip, header — ikut fasa masing-masing.
R8. Bahasa: UI = BM (§16.B), kod/komen/nama = English. Jangan campur.
R9. JANGAN jalankan `php artisan serve` / `npm run dev` / long-running server.
    Dibenarkan: migrate, seed, test, tinker satu-lepas, npm run build, pint.
R10. Selepas setiap fasa: git commit dengan mesej "Fasa N: <ringkasan>".
R11. Versi dikunci (§2.1): Laravel 13, PHP 8.4, Filament ^4 (BUKAN v5),
     Livewire 3, Tailwind 4, Pest. Jika composer/npm menarik versi lain,
     betulkan constraint — jangan ikut arus.
R12. AI client dalam ujian SENTIASA Http::fake() — jangan sekali-kali panggil
     API sebenar dari suite ujian.
```

---

## PROMPT 0 — Fasa 0: Asas Projek

```
Anda akan membina sistem REKA — platform tempahan & penjanaan draf laman web
masjid — berdasarkan spesifikasi lengkap di docs/SPEK-REKA-v1.1.md.

LANGKAH PERTAMA (wajib, sebelum tulis sebarang kod):
1. Baca KESELURUHAN docs/SPEK-REKA-v1.1.md dari atas ke bawah.
2. Kemudian baca semula dengan teliti: §0, §2 (keputusan seni bina), §15
   (jadual fasa), §16.D (env) dan §16.E (struktur folder).
3. Sahkan persekitaran: php -v (mesti 8.3+, sasaran 8.4), composer -V, node -v.
   Jika PHP < 8.3 → BERHENTI dan laporkan (R2).

[SISIPKAN BLOK PERATURAN INDUK §B DI SINI — terpakai untuk semua fasa]

SKOP FASA 0 SAHAJA (§15 baris Fasa 0):
- Projek Laravel 13 baharu (composer create-project laravel/laravel:^13.0).
- Pasang & konfigurasi: Filament ^4 (panel /admin, auth penuh + 2FA wajib —
  paksa persediaan 2FA semasa login pertama), Livewire 3, Tailwind 4 via Vite,
  Pest.
- Struktur folder §16.E (cipta folder + fail placeholder .gitkeep di mana perlu).
- .env.example mengikut §16.D; .env dev boleh guna SQLite.
- Middleware SecurityHeaders mengikut §11.3 (didaftar global; pengecualian
  route draf akan datang di Fasa 8 — jangan bina pengecualian sekarang, cuma
  pastikan seni bina middleware membenarkannya nanti).
- Konfigurasi asas: APP_LOCALE=ms, timezone Asia/Kuala_Lumpur, queue=database.

UJIAN FASA 0 (tulis & luluskan):
- security_headers_present (semak X-Content-Type-Options, Referrer-Policy,
  X-Frame-Options pada respons /)
- admin_requires_authentication (GET /admin → redirect login)
- health check asas: halaman / membalas 200.

VERIFIKASI SEBELUM TAMAT:
php artisan about · php artisan test · npm run build · vendor/bin/pint --test
· composer show laravel/framework filament/filament livewire/livewire
  (sahkan major version tepat R11 — salin output ke laporan).

Keluarkan LAPORAN FASA 0 mengikut format §A.
```

---

## PROMPT 1 — Fasa 1: Pangkalan Data, Model & Seed

```
Sambungan projek REKA. Fasa 0 siap, semua ujian hijau. Peraturan induk R1–R12
dalam PROMPT 0 kekal terpakai — jika anda tidak pasti, baca semula
docs/SPEK-REKA-v1.1.md §0 dan blok peraturan.

BACA DAHULU: §10 (model data — 20 jadual, setiap kolum), §16.A (59 zon JAKIM),
§7.2 (5 pakej reka bentuk), §9.2 (verse library), §12.8 (retensi/prune),
§4.2–4.3 (enum status).

SKOP FASA 1 SAHAJA:
- 20 migrasi §10 — nama kolum, jenis, index, unique, FK TEPAT seperti jadual
  spek. Enum status projek/penjanaan TEPAT seperti §4.2/§4.3.
- Model + casts (encrypted untuk ai_providers.api_key & settings nilai
  is_encrypted, json, enum PHP backed-enum untuk semua status) + relationships
  + method Project::transitionTo() yang menguatkuasakan transisi §4.2 sahaja
  (transisi tidak sah → exception + rekod audit_logs).
- Factories untuk semua model utama.
- Seeders: JakimZoneSeeder (59 baris TEPAT dari §16.A — kod & label; label
  selaras portal rasmi e-solat.gov.my, BUKAN repo acfatah yang lapuk; JANGAN
  tambah/tolak/ubah kod), DesignPackageSeeder (5 pakej §7.2, tokens hex TEPAT),
  VerseLibrarySeeder (1 entri — teks Arab MESTI disalin verbatim dari sumber
  yang §9.2 nyatakan; jika anda tiada akses kepada sumber itu, masukkan
  terjemahan BM + label sumber dan tandakan arabic_text dengan placeholder
  'PENDING_MANUAL_ENTRY' lalu laporkan dalam ⚠️ — JANGAN taip ayat Quran dari
  ingatan anda sendiri, ini R6), SettingsSeeder (§5.3 Settings page — nilai
  lalai).
- Command reka:prune (§12.8) + pendaftaran scheduler (harian).
- Command zones:verify (§16.A): loop semua kod → GET endpoint e-Solat §9.3 →
  status "OK!" = sah, set verified_at; laporan tabular; exit non-zero jika ada
  kegagalan. JANGAN panggil dalam suite ujian (R12) — ujian command ini guna
  Http::fake().

UJIAN FASA 1:
- zones_seed_count_59 · design_packages_seeded_5 · settings_seeded
- project_status_transitions_guarded (laluan sah lulus; laluan tak sah throw)
- api_key_encrypted_at_rest (nilai dalam DB ≠ plaintext)
- prune_removes_expired_leads (freeze masa, cipta lead lama, jalankan command)
- zones_verify_marks_verified (Http::fake respons "OK!")

VERIFIKASI: php artisan migrate:fresh --seed (bersih tanpa ralat) ·
php artisan test (SEMUA, termasuk Fasa 0) · pint --test.
LAPORAN FASA 1.
```

---

## PROMPT 2 — Fasa 2: Corong Lead & Kelayakan

```
Sambungan REKA. Fasa 0–1 siap & hijau. Peraturan induk terpakai (R1–R12).

BACA DAHULU: §5.1 (route awam — setiap medan borang minat), §5.3 baris
LeadResource, §11.2 (rate limit), §13 baris "submitted", §16.B (copy BM),
§1.2 (mesej positioning landing).

SKOP FASA 2 SAHAJA:
- Halaman / (landing): kandungan §5.1 — termasuk ayat positioning §1.2. Reka
  bentuk kemas guna Tailwind, BM penuh, mudah alih dahulu. Jangan over-design;
  fokus jelas & profesional.
- /minat GET+POST: medan & validasi TEPAT §5.1 (regex telefon, dropdown 16
  negeri), honeypot 'website_url' (terisi → HTTP 200 biasa TANPA simpan),
  rate limit 5/min/IP, Turnstile HANYA jika TURNSTILE_SITE_KEY terisi
  (env kosong = langkau sepenuhnya, tiada ralat).
- /minat/terima-kasih. Mail notifikasi admin (ADMIN_NOTIFY_EMAIL) melalui queue.
- Filament LeadResource: kolum, filter status, actions §5.3 — terutamanya
  "Layakkan & Jemput": borang (emel PIC, tempoh token lalai 30 hari, kuota AI
  lalai 3) → transaksi: cipta Project (status invited, salin medan) +
  Invitation (token Str::random(40), SIMPAN HASH SAHAJA §11.1) → hantar
  notifikasi jemputan (guna Mail dahulu; adapter WhatsApp dibina Fasa 9 —
  hantar melalui antara muka Notification supaya saluran WA mudah ditambah
  nanti tanpa ubah kod ini).
- Rekod audit: invitation.created/sent, lead.qualified/rejected.

UJIAN FASA 2:
- lead_form_validates_and_saves · lead_honeypot_silently_drops ·
  lead_rate_limited · turnstile_skipped_when_unconfigured ·
  qualify_creates_project_and_invitation (token plaintext TIDAK disimpan;
  hash sepadan) · qualify_sends_invitation_notification (Notification::fake)

VERIFIKASI: test penuh · pint --test · npm run build.
LAPORAN FASA 2.
```

---

## PROMPT 3 — Fasa 3: Resolusi Token & Pintu Masuk PIC

```
Sambungan REKA. Fasa 0–2 siap & hijau. Peraturan induk terpakai.

BACA DAHULU: §5.2 (middleware resolve.invitation + P1), §11.1 (reka bentuk
token & risiko), §11.2 (rate limit resolusi 10/min/IP), §4.2 (status expired).

SKOP FASA 3 SAHAJA:
- Middleware resolve.invitation: hash SHA-256 token URL → padan invitations
  (belum revoked, belum luput) → gagal: halaman ralat mesra SATU mesej generik
  (jangan bezakan sebab — jangan bocor sama ada token wujud) + rate limit
  resolusi gagal 10/min/IP → 429. Berjaya: kemas kini opened_at (kali
  pertama), last_active_at, opens_count++; kongsi $project & $invitation.
- P1 /b/{token}: skrin selamat datang/sambung §5.2 P1 — bar progres, senarai
  10 langkah + status setiap satu (kira dari project_sections), butang sambung.
  (Halaman langkah sebenar = Fasa 4; buat pautan sedia tetapi route langkah
  boleh placeholder 501 dengan nota "Fasa 4".)
- InvitationResource Filament: kolum + actions Hantar semula / Lanjut tempoh /
  Batalkan (revoke serta-merta) / Salin pautan (jana URL dari token — NOTA:
  token plaintext tidak disimpan, jadi "Salin pautan" hanya tersedia melalui
  butang "Jana token baharu & salin" yang mencipta token baru menggantikan
  hash lama + audit; nyatakan ini dalam UI).
- Scheduler sweep harian: invitation luput + projek masih invited/in_progress
  → status expired (§4.2) + audit.

UJIAN FASA 3:
- token_resolves_valid · token_rejects_expired_and_revoked ·
  token_resolution_rate_limited · token_error_page_is_generic ·
  expired_sweep_transitions_projects · regenerate_token_replaces_hash

VERIFIKASI: test penuh · pint --test.
LAPORAN FASA 3.
```

---

## PROMPT 4 — Fasa 4: Enjin Wizard + Langkah 0–2

```
Sambungan REKA. Fasa 0–3 siap & hijau. Peraturan induk terpakai.

BACA DAHULU: §6 pengenalan + Langkah 0, 1, 2 PENUH (setiap medan, validasi,
help text, peta), §6.11 (matriks preset), §6.13 (autosave), §7 (pakej, ikon,
font, mini-pratonton §7.5), §5.2 P2.

SKOP FASA 4 SAHAJA:
- Enjin wizard: komponen Livewire WizardStep (satu kelas induk + kelas per
  langkah ATAU satu komponen berparameter — pilih satu, konsisten): navigasi
  Kembali/Simpan & Keluar/Seterusnya, autosave §6.13 (blur + debounce 800ms →
  upsert project_sections; indikator "Disimpan ✓ HH:MM"), validasi lembut
  (papar ralat, jangan halang simpan), resume dari P1.
- Langkah 0: kad tier + toggle is_gov TEPAT §6 L0; selepas simpan → apply
  matriks preset §6.11 ke project_pages HANYA jika Langkah 3 belum pernah
  disentuh (simpan penanda dalam project_sections).
- Langkah 1: semua medan §6 L1 — zon: select boleh-cari dari jadual
  jakim_zones ditapis negeri, paparan "KOD — label"; parser GPS "lat, lng"
  + validasi julat Malaysia; datalist authority; logo conditional upload
  (validasi upload penuh datang Fasa 6 — buat validasi asas mime/saiz
  sekarang, tanda TODO-F6 untuk re-encode).
- Langkah 2: pemilih pakej/palet/font/ikon/layout/mood TEPAT §6 L2 + komponen
  x-design-preview §7.5 (Alpine + CSS custom properties; data pratonton =
  nama masjid sebenar dari L1; kad waktu solat = label statik hiasan;
  6 ikon lucide-static §7.3). Pasang lucide-static (npm) dan buat helper
  Blade sisip SVG.

UJIAN FASA 4:
- autosave_persists_section · preset_applied_once_only (edit manual L3 dummy →
  tukar tier → preset TIDAK menulis ganti) · l1_validation_rules (poskod,
  telefon, GPS julat) · zone_options_filtered_by_state ·
  design_selection_saved (pakej + overrides)

VERIFIKASI: test penuh · pint --test · npm run build (pastikan pratonton
tidak memecahkan build).
LAPORAN FASA 4.
```

---

## PROMPT 5 — Fasa 5: Struktur Halaman & Enjin Kandungan (L3–L5)

```
Sambungan REKA. Fasa 0–4 siap & hijau. Peraturan induk terpakai.

BACA DAHULU: §6 Langkah 3 (28 page_key + kluster), Langkah 4 PENUH (setiap
panel — ini jadual terbesar dalam spek, baca baris demi baris), Langkah 5,
§6.11 (semakan silang preset).

SKOP FASA 5 SAHAJA:
- Langkah 3: checklist 28 page_key dikumpul 8 kluster, tooltip "?" setiap
  item, utama+hubungi kekal wajib (tidak boleh nyah-tanda), kaunter "Anggaran
  N halaman", repeater custom max 3. Simpan ke project_pages.
- Langkah 4 — ENJIN SUB-BORANG: akordion; panel muncul HANYA untuk page_key
  aktif yang memerlukan input; status ✓/kosong per panel. Bina SEMUA panel
  §6 L4 dengan medan, enum, had aksara, pra-isi, dan notis TEPAT seperti
  jadual — khususnya: enum kelas Quran dikunci
  (tahsin/hafazan/tadabbur/dhuha/tajwid/ulum/qiraat), templat khidmat
  (nikah/jenazah/tahlil/nasihat berkongsi satu struktur medan), infaq pra-isi
  4 kategori + senarai ikon tertutup 6, checkbox kebenaran galeri, butang
  templat statik (visi/misi contoh, 8 FAQ lazim) — kandungan templat statik
  tulis sendiri dalam BM yang wajar, simpan dalam lang/ms/templates.php,
  senaraikan dalam laporan ⚠️ untuk semakan Azan.
- Langkah 5: semua medan §6 L5 — payment_gateway (5 pilihan + status akaun),
  cms_updater WAJIB, toggles & flags. Flag backlog (tv_display dll) direkod
  sahaja dengan label "akan dibincang".
- Upload dalam panel L4 (foto AJK, QR, PDF khairat, galeri): guna mekanisme
  upload sedia ada Fasa 4 (validasi asas; re-encode penuh = Fasa 6, kekalkan
  TODO-F6).

UJIAN FASA 5:
- pages_checklist_saves_and_counts · mandatory_pages_cannot_be_disabled ·
  l4_panels_conditional_on_pages (tanda nikah → panel nikah muncul; buang →
  hilang, data kekal tersimpan) · quran_class_level_enum_locked ·
  infaq_prefilled_four_categories · gallery_requires_consent_when_files ·
  cms_updater_required

VERIFIKASI: test penuh · pint --test.
LAPORAN FASA 5.
```

---

## PROMPT 6 — Fasa 6: Media, Langkah Akhir, Semakan & Hantar

```
Sambungan REKA. Fasa 0–5 siap & hijau. Peraturan induk terpakai.

BACA DAHULU: §6 Langkah 6–9, §6.12 (formula skor + DUA gate berbeza: Hantar
vs Jana), §11.4 (upload — re-encode, EXIF, MIME sebenar), §5.2 P3, §12.4
(teks consent penuh dari §16.B).

SKOP FASA 6 SAHAJA:
- Perkhidmatan upload terpusat (app/Services/UploadService.php): finfo MIME
  sebenar, had saiz per jenis, imej → re-encode Intervention Image + BUANG
  EXIF termasuk GPS + resize sisi panjang (2400px hero / 1200px lain), pdf →
  semak magic bytes %PDF, nama fail ULID, simpan luar webroot, hidang melalui
  route bertoken. GANTIKAN semua TODO-F6 Fasa 4–5 dengan servis ini.
  Driver imej: KUNCI GD (lalai — metadata memang terbuang semasa re-encode);
  jika Imagick digunakan, strip MESTI eksplisit — rujuk andaian §11.4 spek.
  Ujian EXIF menguatkuasakan HASIL, bukan mekanisme.
- Langkah 6 (hero_mode + fail), Langkah 7 (rujukan), Langkah 8 (teknikal —
  semua radio bersyarat §6 L8), Langkah 9 (nota, budget_hint, perakuan PIC
  pra-isi dari invitation, DUA checkbox dengan teks TEPAT §16.B).
- CompletenessService (§6.12): kira set medan wajib AKTIF secara dinamik
  (bergantung halaman ditanda & mod dipilih) → skor; senarai "belum lengkap"
  dengan label BM + anchor terus ke medan.
- P3 /semak: kad ringkasan per langkah + Edit, skor, senarai kekurangan,
  butang Hantar aktif @100% → status submitted + notifikasi admin + banner
  "masih boleh edit sehingga diluluskan".
- No. akaun bank pada paparan semak: mask ••••1234 (§11.1).

UJIAN FASA 6:
- upload_rejects_bad_mime_and_size (fail .php dinamakan .jpg → tolak) ·
  upload_strips_exif_gps (fixture berimej EXIF GPS → output tiada GPS) ·
  upload_reencodes_image · completeness_score_correct (3 senario: surau
  minimum, kariah dengan 6 panel, besar penuh — kira tangan dahulu dalam
  komen ujian, kemudian assert) · submit_blocked_below_100 ·
  submit_sets_status_and_notifies · review_masks_bank_account

VERIFIKASI: test penuh · pint --test.
LAPORAN FASA 6.
```

---

## PROMPT 7 — Fasa 7: Modul AI & Penjanaan Draf  ⚠️ FASA PALING KRITIKAL

```
Sambungan REKA. Fasa 0–6 siap & hijau. Peraturan induk terpakai — khususnya
R6 (guardrail agama) dan R12 (Http::fake dalam ujian).

BACA DAHULU: §8 KESELURUHAN (8.1–8.8, termasuk prompt verbatim 8.3 & urutan
validasi 8.4), §9 KESELURUHAN, §5.2 P4, §5.3 AiProviderResource, §12.7
(PII-minimized).

SKOP FASA 7 SAHAJA:
- AiProviderResource Filament §5.3 (api_key write-only paparan ••••+4, action
  "Uji Sambungan" = panggilan mini sebenar bila diklik admin). Placeholder
  medan model (rujukan Julai 2026, admin isi nilai sebenar): claude-sonnet-5 /
  claude-haiku-4-5 / claude-opus-4-8 / glm-5. JANGAN hard-code model dalam kod.
- NOTA response_format (§8.1): OpenAI menolak json_object jika perkataan
  "json" tiada dalam mesej — system prompt §8.3 memang mengandunginya;
  JANGAN buang/ubah perkataan itu semasa menyalin prompt verbatim.
- interface AiClient + AnthropicClient + OpenAiCompatibleClient — payload,
  header, parsing TEPAT §8.1 (termasuk fallback buang response_format bila
  provider menolak; timeout dari config; TIADA retry di lapisan client).
- PromptBuilder: system prompt DISALIN VERBATIM dari §8.3 ke
  resources/prompts/draft-system.txt (jangan tulis semula ikut gaya sendiri);
  user prompt Blade §8.3 — PII-minimized §12.7 (senarai JANGAN-masuk dipatuhi
  bulat-bulat); skema output dibina dinamik ikut halaman aktif §8.2; mod
  content_tweak menambah blok arahan tweak.
- DraftContentValidator §8.4 — LIMA semakan DALAM URUTAN SPEK, termasuk
  regex julat aksara Arab yang DITULIS TEPAT seperti spek. Gagal = gagal
  percubaan penuh, bukan "baiki sendiri".
- DraftRenderer + resources/views/draft/shell.blade.php §8.5: HTML
  lengkap-kendiri, CSS tulen dengan custom properties tokens, seksyen ikut
  project_pages, blok waktu solat STATIK berlabel (teks label TEPAT §8.5),
  ayat Arab HANYA dari verse_library aktif, suntikan server watermark/banner/
  noindex (bukan pilihan), penanda "✎ Dijana AI" pada medan origin ai,
  simpan snapshot storage/app/drafts/.
- GenerateDraftJob §8.6 — TUJUH langkah tepat: TX lock + semak gate/kuota/
  cooldown/kunci → queued → processing step 1..4 → retry dalaman 30s/90s →
  kuota HANYA selepas berjaya → failed = refund + mail admin. Siling
  keselamatan 10 jana/hari/projek (§11.2).
- P4 /jana: kad kuota, cooldown countdown, butang dengan sebab dinyahaktif,
  progres 4 peringkat (poll 3s), elapsed, mesej >5 minit, senarai versi.
- Ledger kos §8.8 (kadar dari ai_providers.meta; JANGAN hard-code harga).

UJIAN FASA 7 (semua dengan Http::fake / Queue partial):
- provider_test_connection_action · generation_lock_prevents_concurrent ·
  generate_blocked_before_submit · quota_increment_only_on_success ·
  failed_generation_refunds_quota_and_mails_admin · cooldown_enforced ·
  daily_ceiling_enforced · arabic_output_rejected (fixture JSON sah TETAPI
  mengandungi satu aksara Arab → reject) · length_violation_rejected ·
  unknown_key_rejected · draft_contains_watermark_and_noindex ·
  draft_prayer_block_is_labeled_static · pii_never_in_prompt (assert
  payload Http::fake TIDAK mengandungi telefon PIC/akaun bank) ·
  cost_ledger_recorded

VERIFIKASI: test penuh · pint --test. Dalam laporan: tampal 20 baris pertama
HTML draf yang dijana ujian (bukti watermark + noindex).
LAPORAN FASA 7.
```

---

## PROMPT 8 — Fasa 8: Pemapar Draf, Tweak, Kelulusan & Pakej Serahan

```
Sambungan REKA. Fasa 0–7 siap & hijau. Peraturan induk terpakai.

BACA DAHULU: §5.2 P5–P9, §8.7 (jadual kuota — beza tweak reka vs kandungan),
§14 KESELURUHAN (ZIP, spec.json, build-brief templat Blade DUA MOD,
sanity-seed 25 skema), §12.4 (perakuan).

SKOP FASA 8 SAHAJA:
- P5 pemapar: iframe sandbox="" memuatkan P6, togol Mobile/Desktop
  (390px/100%), bar tindakan 4 butang §5.2.
- P6 draf mentah: header CSP TEPAT §5.2 P6 + kelonggaran fonts Google §7.4
  + X-Robots-Tag — ini pengecualian SecurityHeaders yang ditangguh dari
  Fasa 0; laksanakan dengan kemas.
- P7 tweak reka bentuk: komponen pemilih sama Fasa 4 → render semula
  serta-merta TANPA AI, quota_design_used++ (had 5), generations jenis
  design_render (guna output_json sedia ada, tokens baharu).
- P8 tweak kandungan: borang berstruktur §5.2 P8 → GenerateDraftJob
  content_tweak (kuota AI, cooldown — logik Fasa 7 diguna semula, jangan
  duplikasi).
- P9 kelulusan: borang perakuan + 2 checkbox teks §16.B → modal muktamad →
  approvals dengan SNAPSHOT BEKU (SpecBuilder penuh §14.2 + draft path +
  hash fail draf) + IP/UA → status approved → wizard & tweak jadi baca-sahaja
  (banner §5.2) → notifikasi admin.
- SpecBuilder §14.2 (struktur kunci TEPAT — ini kontrak dengan build-brief),
  SanitySeedBuilder §14.4 (_type/_id deterministik, satu JSON per baris),
  build-brief.blade.php §14.3 (MOD A + MOD B, bahagian tetap termasuk salinan
  peraturan §9, senarai ai_flags), README-HANDOVER.
- Filament ProjectResource action "Eksport Pakej Serahan" (approved+ sahaja):
  bina ZIP §14.1 → handover_exports → muat turun → status handover_exported.

UJIAN FASA 8:
- design_rerender_no_ai_no_quota (assert Http TIDAK dipanggil) ·
  design_quota_capped_at_5 · content_tweak_uses_ai_quota ·
  approval_freezes_snapshot_and_locks_wizard (cuba edit selepas lulus → 403/
  redirect baca-sahaja) · approval_records_identity_ip ·
  spec_json_matches_schema (kunci peringkat atas TEPAT §14.2) ·
  sanity_ndjson_valid (setiap baris json_decode berjaya + _type dikenali) ·
  handover_zip_contains_all_artifacts (6 item §14.1) ·
  build_brief_contains_real_values (tiada placeholder {{}} tinggal)

VERIFIKASI: test penuh · pint --test.
LAPORAN FASA 8.
```

---

## PROMPT 9 — Fasa 9: Notifikasi, Status PIC, Admin Penuh & Pengerasan

```
Sambungan REKA. Fasa 0–8 siap & hijau. Peraturan induk terpakai.

BACA DAHULU: §13 (adapter WA + matriks 9 event + templat §16.B), §5.2
P10–P11, §5.3 (ProjectResource penuh, Dashboard, Settings), §11.3 (mask
logging), §12.2/§12.5 (privasi dwibahasa, SOP), §16.C–E.

SKOP FASA 9 SAHAJA:
- WhatsappGateway §13: POST JSON + X-Gateway-Secret, queued tries=3
  backoff=60, log notification_logs, gagal → fallback mail, TIDAK menyekat
  aliran. URL+secret dari Settings.
- Semua 9 event §13 didaftar pada titik masing-masing (sesetengah sudah
  wujud sebagai mail — naik taraf kepada Notification dwi-saluran) +
  scheduler: reminder 3 hari idle (max 2×), token.expiring 5 hari.
- P10 /status: timeline §5.2 + thread nota dua hala; P11 POST nota (rate
  limit §11.2); balasan admin dari tab projek.
- ProjectResource LENGKAP §5.3: semua tab, top-up kuota (+N, audit), tukar
  status (dropdown transisi sah SAHAJA — guna transitionTo), buka wizard
  sebagai PIC.
- Dashboard widgets §5.3 (corong, kos bulan, queue health, token hampir
  luput) + Settings page penuh.
- /privasi + /terma dwibahasa (kandungan mengikut §12.2 — tulis draf penuh
  BM+EN berdasarkan peta data §12.3; tanda dalam laporan ⚠️ untuk semakan
  perundangan Azan) + docs/SOP-PELANGGARAN-DATA.md §12.5 (kandungan tambahan
  v1.1: lampiran templat TIA 1-muka §12.7 + perenggan penilaian ADMP/DPIA dan
  pemetaan DPbD §12.10; termasuk query
  eksport subjek terjejas).
- Pengerasan akhir: helper mask() + audit semua Log:: sedia ada (grep;
  senaraikan yang dibetulkan), semak semua audit event §10 direkod,
  deploy/{nginx.conf, supervisor.conf (queue:work --queue=ai,default),
  cron, backup.sh} §11.5, README projek (setup, deploy, operasi).

UJIAN FASA 9:
- whatsapp_adapter_posts_with_secret (Http::fake) ·
  whatsapp_failure_falls_back_to_mail_and_logs ·
  all_nine_events_dispatch (Notification::fake per event) ·
  reminder_scheduler_max_twice · notes_thread_two_way ·
  admin_status_change_respects_transitions · topup_increases_quota_with_audit
  · logs_never_contain_full_phone_or_token (ujian helper mask + sampel)

VERIFIKASI AKHIR FASA: php artisan test — SEMUA 26+ ujian §15.11 dan semua
ujian fasa WAJIB hijau · pint --test · npm run build · php artisan migrate:fresh
--seed bersih. Tampal ringkasan kiraan ujian penuh dalam laporan.
LAPORAN FASA 9.
```

---

## PROMPT 10 — Fasa 10: AUDIT AKHIR & SEDIA LIVE (fasa tambahan — verifikasi menyeluruh)

```
Sambungan REKA. Fasa 0–9 siap, semua ujian hijau. Ini fasa AUDIT — matlamat:
buktikan sistem sedia produksi, cari apa yang TERTINGGAL, bukan menambah ciri.

LAKUKAN DALAM URUTAN:

1. AUDIT KEPATUHAN SPEK (spek → kod):
   Buka docs/SPEK-REKA-v1.1.md dan semak SATU-PERSATU senarai berikut wujud
   & berfungsi dalam kod — hasilkan jadual ✅/❌ dengan lokasi fail:
   a. Setiap route §5.1, §5.2 (P1–P11), setiap resource/action §5.3
   b. Setiap medan wizard §6 L0–L9 (semak nama, wajib, validasi, enum)
   c. 28 page_key §6 L3 · matriks preset §6.11 · dua gate §6.12
   d. 5 pakej + 24 ikon + 4 pasangan font §7
   e. §8: urutan job 7 langkah, urutan validasi 5 semakan, regex Arab,
      jadual kuota §8.7
   f. §9: TIGA lapis guardrail · §11: SEMUA baris jadual rate limit §11.2
   g. §10: 20 jadual — bandingkan migrasi dengan jadual spek kolum demi kolum
   h. §13: 9 event · §14: 6 artifak ZIP + kunci spec.json
   Mana-mana ❌ → BAIKI SEKARANG + tambah ujian menutupnya. Jika ❌ itu
   disebabkan percanggahan spek → R2: BERHENTI dan tanya.

2. AUDIT KUALITI KOD:
   composer audit · npm audit (laporkan; naik taraf patch sahaja) ·
   pint --test · cari & hapus: dd(), dump(), TODO-F6 tertinggal, route
   placeholder 501, komen "sementara".

3. SKRIP QA MANUAL HUJUNG-KE-HUJUNG (jalankan sendiri sepenuhnya guna
   sqlite/mysql dev + Http::fake provider melalui provider palsu "Dev Fake"
   yang anda daftar dalam seeder KHAS persekitaran local sahaja):
   lead → qualify → buka token → isi wizard minimum tier surau (setiap
   langkah) → hantar → jana → siap → tweak reka → tweak kandungan → lulus →
   eksport ZIP → buka ZIP & sahkan 6 artifak + build-brief tiada slot kosong
   → semak P10 + nota dua hala → semak semua notification_logs terhasil.
   Rekod SETIAP langkah (perintah/route + hasil) dalam docs/QA-RUN-F10.md.

4. SENARAI SEMAK GO-LIVE (hasilkan docs/GO-LIVE-CHECKLIST.md — untuk Azan,
   bukan untuk anda laksanakan):
   ☐ Server: PHP 8.4, MySQL, nginx (guna deploy/nginx.conf), HTTPS + HSTS
   ☐ .env produksi: APP_ENV=production, APP_DEBUG=false, APP_KEY baharu,
     mail sebenar, ADMIN_NOTIFY_EMAIL
   ☐ Supervisor queue worker aktif · cron schedule:run · backup.sh diuji
     PULIH (restore test, bukan sekadar backup)
   ☐ php artisan zones:verify — 59/59 lulus (WAJIB sebelum prospek pertama)
   ☐ Admin: akaun + 2FA · Settings: gateway WA URL+secret (uji 1 mesej
     sebenar) · Provider AI sebenar + "Uji Sambungan" + kadar harga diisi
     dalam meta (§8.8)
   ☐ Padam provider "Dev Fake" · migrate --force · storage:link
   ☐ Notis privasi: nama perniagaan sebenar diisi (REKA_BUSINESS_NAME)
   ☐ Ujian penuh corong dengan telefon sebenar Azan sebagai "PIC ujian"
     SEBELUM jemput PIC sebenar pertama

5. LAPORAN AKHIR: jadual audit (1), keputusan QA (3), baki risiko/nota
   jujur — JANGAN tulis "100% sedia" jika ada apa-apa ⚠️; senaraikan.
```

---

## §C — NOTA JUJUR UNTUK AZAN (baca sekali)

- **"Siap semua fasa = boleh live tanpa masalah"** — proses ini (spek terperinci + fasa berpagar + 60+ ujian + regresi setiap fasa + audit Fasa 10 + QA manual) menekan risiko bug ke tahap minimum yang praktikal, tetapi TIADA perisian sifar-bug di dunia nyata. Sebab itu Fasa 10 memaksa ujian corong penuh dengan awak sendiri sebagai PIC ujian sebelum PIC sebenar pertama. Jangkakan 1–2 isu kecil kosmetik/UX ditemui di situ — itu normal dan murah dibaiki pada peringkat itu.
- **Kos konteks:** setiap prompt menyuruh Claude Code baca semula seksyen spek berkaitan — ini disengajakan (anti-drift), walaupun menambah sedikit masa setiap fasa.
- Jika Claude Code mula "mereka bentuk sendiri" di luar spek → hentikan, paste semula BLOK PERATURAN §B, dan minta dia rujuk nombor seksyen untuk setiap keputusan.
