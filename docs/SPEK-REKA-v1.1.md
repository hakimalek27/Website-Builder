# SPEK SISTEM **REKA** v1.1
## Platform Tempahan & Penjanaan Draf Laman Web Masjid (Multi-Prospek)

**Tarikh spek:** 7 Julai 2026 (v1.1 — dikemaskini hari sama selepas semakan fakta bebas kedua; lihat SEJARAH VERSI di hujung) · **Pemilik:** Azan · **Status:** MUKTAMAD — sedia untuk pelaksanaan one-shot oleh Claude Code
**Nama kerja:** REKA (boleh ditukar; semua rujukan dalaman guna `reka`)

> Dokumen ini adalah SUMBER TUNGGAL KEBENARAN untuk MVP. Semua fakta luaran (API JAKIM, PDPA, versi framework, zon solat, e-Invois) telah **disahkan pada 7 Julai 2026** melalui ujian langsung/carian — lihat tanda `[DISAHKAN]`. Tiada apa dalam dokumen ini yang bersifat andaian tanpa ditanda `[ANDAIAN]` atau `[SAHKAN SEMULA]`.

---

# §0 — ARAHAN PELAKSANAAN UNTUK CLAUDE CODE (PROTOKOL ONE-SHOT)

Claude Code: baca keseluruhan dokumen ini SEBELUM menulis sebarang kod. Kemudian:

1. **Bina mengikut urutan Fasa 0 → Fasa 9** (§15). Jangan langkau fasa. Setiap fasa ada *Definition of Done* — sahkan sebelum ke fasa seterusnya.
2. **Jangan tanya soalan.** Semua keputusan telah dibuat dalam dokumen ini. Jika ada percanggahan, keutamaan: §2 Keputusan Seni Bina > §6 Wizard > lain-lain.
3. **Jangan tambah ciri di luar skop MVP** (§1.3). Ciri "bagus untuk ada" berada dalam §17 Backlog — JANGAN bina.
4. **Semua teks UI dalam Bahasa Melayu** mengikut §16.C (copy strings). Kod, komen, nama variable dalam English.
5. **Jangan jalankan `npm run dev`/`php artisan serve`** — pemilik akan jalankan sendiri. Boleh jalankan `php artisan migrate:fresh --seed`, `php artisan test`, `npm run build`.
6. **Ujian wajib lulus:** senarai Pest dalam §15.11. Fasa 9 tidak siap selagi semua hijau.
7. **Fail `.env`:** salin dari `.env.example` (§16.D), isi nilai placeholder yang munasabah untuk dev (SQLite dibenarkan untuk dev; produksi MySQL).
8. Data agama adalah sensitif: **patuhi §9 Guardrail Agama tanpa pengecualian.** Ini bukan pilihan teknikal, ini syarat produk.

---

# §1 — RINGKASAN PRODUK

## 1.1 Masalah & Penyelesaian

**Masalah (disahkan melalui kajian 5 laman web masjid sebenar):** Masjid Malaysia mahu laman web tetapi (a) tidak tahu apa yang mereka mahu/perlukan, (b) tiada kemampuan menulis kandungan, (c) laman sedia ada terbiar/digodam (mtajbj.gov.my: footer disuntik link spam) atau tidak siap (masjidklcc.com.my: gambar placeholder "logo-temp" masih dalam produksi walaupun masjid ikonik).

**Penyelesaian:** Sistem intake bertoken di mana admin (Azan) menjemput PIC masjid yang layak → PIC mengisi wizard berpandu 10 langkah (dengan preset, contoh, dan pilihan visual) → sistem menjana **draf laman** melalui AI (API key admin, kuota terkawal) → PIC semak/tweak/lulus → sistem menghasilkan **pakej serahan** (spec.json + build-brief.md + sanity-seed.ndjson + aset) → Azan menjalankan Claude Code (akaun berasingan) untuk membina laman sebenar.

**Prinsip teras:** *Setiap medan wizard wujud kerana Claude Code memerlukannya untuk membina laman dengan tepat.* Output sistem = spesifikasi lengkap, bukan sekadar borang.

## 1.2 Positioning vs Pesaing (kajian pasaran 7 Julai 2026)

| Pesaing | Model | Kelemahan yang REKA eksploitasi |
|---|---|---|
| **masaj.id (MASAJID)** | Percuma, subdomain, template seragam, fokus paparan TV waktu solat, 5,000+ masjid | Semua laman nampak sama; tiada laman custom, tiada domain sendiri, tiada kandungan penuh |
| **masjidmalaysia.org** | WordPress templated (powers mtajbj.gov.my) | Laman kliennya digodam (bukti terbiar); stack lama `[SAHKAN SEMULA: status operasi semasa laman ini belum disahkan]` |
| **2en Apps** | Joomla (powers masjidwilayah.gov.my) | Stack legacy, bukan self-serve |
| **Direktori JAKIM/mymasjid** | Direktori sahaja | Bukan laman web penuh |

**Jawapan REKA kepada "kenapa bayar bila masaj.id percuma?":** laman custom penuh identiti masjid sendiri (domain, reka bentuk, kandungan lengkap, sistem infaq/kariah) berbanding template seragam. Ayat ini WAJIB ada pada landing page.

## 1.3 Skop MVP — apa yang DIBINA vs TIDAK

**DIBINA (MVP):**
- Landing awam + borang Daftar Minat (lead)
- Panel admin Filament: leads, projek, jemputan bertoken, penyedia AI, monitor penjanaan, kos, nota, eksport serahan
- Wizard 10 langkah bertoken dengan autosave, resume, preset tier, pemilih pakej reka bentuk dengan pratonton hidup
- Enjin penjanaan draf: AI mengeluarkan **JSON kandungan sahaja** → Blade merender draf HTML (seni bina 2-lapis, §2.4)
- Kuota (1 jana + 2 tweak AI), kunci, cooldown, refund kegagalan
- Kelulusan beku (snapshot) + pakej serahan ZIP
- Notifikasi email + adapter WhatsApp generik (wassap.wehdah.my)
- Halaman privasi/terma dwibahasa, pematuhan PDPA (§12)

**TIDAK DIBINA (MVP) — berada di §17 Backlog:**
- Import Facebook automatik (kod sedia ada di repo mamkl, port kemudian)
- Bantuan AI *dalam* wizard (karang teks masa isi) — MVP guna templat statik boleh-edit, kos AI = RM0 semasa mengisi
- Mod paparan TV, PWA, portal warga, pembayaran deposit, multi-admin, komen AJK, screenshot draf automatik
- **Master template repo `masjid-template`** — projek berasingan (§2.5), bukan sebahagian MVP ini

---

# §2 — KEPUTUSAN SENI BINA (LOCKED — jangan ubah)

## 2.1 Stack `[DISAHKAN 7 Julai 2026]`

| Komponen | Pilihan | Justifikasi (fakta disahkan) |
|---|---|---|
| Framework | **Laravel 13** (keluaran 17 Mac 2026) | Zero breaking changes dari L12 (pengetahuan DIWAN Azan terpakai terus); tempoh sokongan L12 bug-fix tamat **13 Ogos 2026** — projek baru pada Julai 2026 mesti mula di L13 |
| PHP | **8.4** (minimum 8.3) | L13 memerlukan 8.3+; 8.4 disyorkan untuk L13.3+ |
| Admin | **Filament ^4** | Kepakaran sedia ada Azan (DIWAN). Filament v5 (Jan 2026) = v4 + Livewire 4 sahaja, tiada perubahan fungsi — kekal v4 untuk kebolehramalan one-shot. Ekosistem (Filament/Livewire) serasi L13 sejak hari pelancaran |
| UI awam/PIC | **Livewire 3 + Alpine + Tailwind 4 (Vite)** | Konsisten dengan Filament 4 (Livewire 3); tiada framework JS berasingan |
| DB | **MySQL 8 / MariaDB 10.11+** (dev: SQLite) | VPS Tencent sedia ada |
| Queue | **database driver** + `php artisan queue:work` bawah Supervisor | Tiada dependensi Redis untuk MVP |
| Storage | **local disk** (`storage/app`), symlink public untuk aset yang perlu dipapar | Self-host; tiada S3 |
| Ujian | **Pest** | Standard Laravel semasa |
| AI client | **Adapter nipis buatan sendiri** (Laravel `Http`), 2 driver: `anthropic` + `openai_compatible` | Meliputi Claude, OpenAI, GLM, OpenRouter, Ollama (semuanya OpenAI-compatible melalui `base_url`). Nota: Laravel 13 ada AI SDK first-party (OpenAI/Anthropic/Gemini) — **JANGAN guna untuk MVP**: SDK kini stabil produksi `[DISAHKAN]`, tetapi sintaks `base_url` custom (Ollama/GLM/OpenRouter) masih belum disahkan dalam dokumentasi rasmi; adapter sendiri ±150 baris, deterministik, boleh diuji |

## 2.2 Prinsip sistem

1. **Satu aplikasi Laravel** — awam, PIC (bertoken), admin (Filament) dalam satu codebase. Multi-prospek ≠ multi-tenant penuh; setiap masjid = satu `project`, TIADA keperluan pengasingan pangkalan data per masjid untuk sistem intake ini.
2. **Setiap medan wizard → kunci `spec.json` → skema Sanity/variable build-brief.** Pemetaan didokumentasi dalam §6 (kolum "Peta").
3. **Operasi mahal diasingkan:** borang lead percuma & terbuka; wizard bertoken; panggilan AI dikawal kuota + kunci + cooldown; Claude Code (bina sebenar) langsung di luar sistem ini, akaun/kunci berasingan — **kunci API sistem TIDAK PERNAH sama dengan akaun Claude Code Azan.**
4. **Tiada bayaran dalam sistem.** Caj perkhidmatan diurus luar sistem (invois manual/e-Invois — lihat §12.9).

## 2.3 Dua AI berasingan (keputusan pemilik, dikekalkan)

| | AI Sistem (draf) | Claude Code (bina sebenar) |
|---|---|---|
| Kunci | `ai_providers` (DB, disulitkan), dikonfigurasi admin | Akaun peribadi Azan, luar sistem |
| Tugas | Jana JSON kandungan draf sahaja | Bina laman produksi dari pakej serahan |
| Pencetus | PIC (dengan kuota) | Azan sahaja |
| Kos | Diledger per projek (§8.8) | Luar skop sistem |

## 2.4 ★ PENAMBAHBAIKAN UTAMA — Seni bina draf 2-lapis

**Keputusan:** AI **tidak** menulis HTML. AI mengeluarkan **JSON kandungan** mengikut kontrak ketat (§8.2); server merender draf melalui **Blade shell deterministik** (`resources/views/draft/shell.blade.php`) yang membaca design tokens pakej pilihan (§7).

Kenapa (berbanding "AI jana HTML penuh"):
- **Kos:** output ±2–4k token (JSON) vs 10k+ (HTML) — anggaran <RM0.50/jana dengan model kelas Sonnet `[ANDAIAN harga; sahkan harga semasa provider]`
- **Konsistensi:** draf SENTIASA mengikut tema/ikon/font pilihan PIC; mustahil AI "melencong" reka bentuk
- **Keselamatan:** tiada HTML/JS dari AI = tiada isu sanitasi/XSS; validasi = JSON schema + peraturan §8.4
- **Tweak reka bentuk PERCUMA:** tukar pakej/warna/font = render semula Blade, **sifar panggilan AI, sifar kuota**
- **Deterministik untuk one-shot:** Blade shell ditulis sekali oleh Claude Code, boleh diuji

Draf akhir = satu fail HTML lengkap-kendiri (CSS sebaris melalui CSS custom properties daripada tokens, imej melalui URL storage aplikasi), disimpan sebagai snapshot, dipapar dalam iframe `sandbox` dengan CSP ketat, dan **sentiasa** disuntik banner + watermark "DRAF SAMPEL" oleh server (bukan pilihan AI).

## 2.5 ★ PENAMBAHBAIKAN — Strategi master template untuk bina sebenar

**Masalah jujur:** 50 masjid = 50 codebase Next.js berasingan = beban selenggara yang akan membunuh perniagaan ini perlahan-lahan.
**Keputusan:** laman sebenar dibina daripada **satu repo `masjid-template`** (generalisasi mamkl.my: Next.js 16 + Sanity + 25 skema sedia ada) — per masjid = tokens + kandungan + section flags, BUKAN kod unik. `build-brief.md` (§14.3) ditulis dalam **dua mod**: Mod A (template — disyorkan) dan Mod B (dari kosong, mengikut struktur 9-prompt sedia ada Azan) sebagai fallback sehingga template siap. Penjanaan template itu sendiri = projek berasingan ±3–5 hari `[LUAR SKOP MVP]`.

---

# §3 — PERANAN & PERSONA

| Peranan | Akses | Nota |
|---|---|---|
| **Admin (Azan)** | Filament `/admin`, 2FA wajib | Satu-satunya pengguna berdaftar MVP. Qualify lead, jana/batal token, konfigurasi AI, top-up kuota, eksport serahan, kemas kini status binaan |
| **PIC masjid** | URL bertoken `/b/{token}` sahaja — TIADA akaun, TIADA kata laluan | Individu yang diberi kuasa AJK. Mengisi wizard, menjana draf (kuota), tweak, lulus. Boleh kongsi link kepada AJK lain untuk semakan (link = akses; lihat mitigasi §11.1) |
| **Pengunjung awam** | Landing + borang minat sahaja | Lead sahaja; tiada akses wizard |
| **AJK lain** | Melalui link PIC (baca sama seperti PIC) | MVP tiada peranan berasingan; mod komen = Backlog |

---

# §4 — ALIRAN END-TO-END & STATE MACHINE

## 4.1 Naratif penuh (12 langkah)

1. **Minat:** Pengunjung isi `/minat` (nama masjid, negeri, nama PIC, telefon, catatan). → rekod `leads`, notifikasi admin.
2. **Kelayakan:** Admin semak lead di Filament → tindakan **"Layakkan & Jemput"** → sistem cipta `project` + `invitation` (token 40-aksara, hash disimpan, luput lalai 30 hari) → hantar WhatsApp + email kepada PIC dengan link `/b/{token}`.
3. **Isi wizard:** PIC buka link (bila-bila, boleh sambung — autosave setiap langkah). Langkah 0 (tier) → 9 (perakuan). Status projek `in_progress`.
4. **Semak & hantar:** Halaman `/semak` menunjukkan ringkasan + skor kelengkapan. Butang **Hantar** aktif hanya pada 100% medan wajib → status `submitted`, notifikasi admin.
5. **Jana draf (PIC, kuota 1):** Butang **"Jana Draf"** di `/jana` → semakan gate + kunci + cooldown → `GenerateDraftJob` dibaris → skrin progres 4 peringkat (polling 3s) → siap: notifikasi WhatsApp "Draf sedia" → status `draft_ready`.
6. **Semak draf:** PIC lihat draf (iframe, watermark). Tindakan: **Lulus** / **Tweak reka bentuk** (percuma, render semula) / **Tweak kandungan** (AI, kuota 2) / **Nota kepada admin**.
7. **Tweak (pilihan):** Borang tweak berstruktur (§5.2 P8). Kandungan → jana semula JSON (versi baru disimpan). Reka bentuk → render semula serta-merta.
8. **Habis kuota:** Butang jana AI dilumpuhkan → borang "Nota kepada Admin" (kategori + teks). Admin boleh **Top-up +N** dari Filament.
9. **Lulus:** PIC klik **Luluskan Draf Ini** → modal perakuan (nama, jawatan, telefon, 2 checkbox §12.4) → `approvals` merekod **snapshot beku** (spec penuh + rujukan draf + identiti + masa + IP) → status `approved`, notifikasi admin.
10. **Eksport serahan:** Admin klik **"Eksport Pakej Serahan"** → ZIP (§14) dijana → status `handover_exported`.
11. **Bina sebenar:** Azan jalankan Claude Code (luar sistem) dengan pakej tersebut. Admin kemas kini status manual: `in_build` → `in_review` → `live`.
12. **Jejak PIC:** Sepanjang #10–11, PIC melihat status + thread nota di `/b/{token}/status`.

## 4.2 State machine — `projects.status`

```
invited ──buka link──▶ in_progress ──hantar (gate 100%)──▶ submitted
submitted ──jana pertama berjaya──▶ draft_ready
draft_ready ──tweak AI berjaya──▶ draft_ready   (kekal; versi generation baharu)
draft_ready ──lulus──▶ approved ──eksport──▶ handover_exported
handover_exported ──admin──▶ in_build ──admin──▶ in_review ──admin──▶ live ──admin──▶ archived
[mana-mana sebelum approved] ──admin──▶ cancelled
invited/in_progress ──token luput tanpa aktiviti──▶ expired (sweep harian; admin boleh lanjut token → kembali)
```

**Peraturan transitions (enforce dalam kod, method `Project::transitionTo()`):** hanya laluan di atas sah; percubaan lain → exception + `audit_logs`. `approved` adalah TITIK BEKU: selepas ini wizard menjadi baca-sahaja untuk PIC.

## 4.3 State machine — `generations.status`

```
queued ─▶ processing ─▶ succeeded
                     └▶ failed (selepas 2 retry backoff 30s/90s) — kuota DIPULANGKAN, admin dimaklumkan
progress_step semasa processing: 1 Menganalisa maklumat · 2 Menyusun kandungan · 3 Menyemak & memurnikan · 4 Menjana paparan
```

**Kunci:** satu baris `generations` berstatus `queued|processing` bagi satu projek = kunci. Semakan & insert dalam SATU transaksi DB (`lockForUpdate` pada project). Butang UI juga dilumpuhkan, tetapi kunci sebenar adalah di server.

---

# §5 — PETA LAMAN PENUH (setiap route, setiap fungsi)

## 5.1 Awam (tiada auth)

| Route | Method | Kandungan & fungsi |
|---|---|---|
| `/` | GET | **Landing.** Hero nilai tawaran ("Laman web rasmi masjid anda — direka khusus, bukan template"), 3 langkah cara ia berfungsi, perbandingan ringkas vs platform percuma (§1.2), pautan contoh rujukan (mamkl.my dll — pautan luar sahaja, tiada screenshot pihak ketiga), CTA → `/minat` |
| `/minat` | GET, POST | **Borang Daftar Minat** (satu skrin): `mosque_name*`, `state*` (dropdown 16), `pic_name*`, `pic_phone*` (format 01x, validasi regex `^01[0-9]{8,9}$`), `pic_email`, `current_website` (url, nullable), `notes` (max 500). **Anti-spam:** honeypot field tersembunyi `website_url` (jika terisi → terima senyap tanpa simpan), rate limit `5/min/IP`, Turnstile Cloudflare pilihan (env-gated `TURNSTILE_SITE_KEY`; jika kosong → dilangkau). POST berjaya → simpan `leads`, notifikasi admin (mail), redirect `/minat/terima-kasih` |
| `/minat/terima-kasih` | GET | Pengesahan + ekspektasi ("Kami akan hubungi dalam 2 hari bekerja") |
| `/privasi` | GET | Notis Privasi **dwibahasa BM + EN** (§12.2) — kandungan statik Blade |
| `/terma` | GET | Terma perkhidmatan ringkas dwibahasa |

## 5.2 PIC (middleware `resolve.invitation`)

**Middleware:** ambil `{token}` → `hash('sha256', token)` → cari `invitations` yang `revoked_at IS NULL` dan `expires_at > now()` → jika gagal: halaman ralat mesra "Pautan tidak sah atau telah luput — hubungi kami" (TANPA membezakan sebab; jangan bocor maklumat). Jika berjaya: kemas kini `last_active_at`, `opened_at` (kali pertama), `opens_count++`; kongsi `$project` ke semua view. Semua route di bawah berprefix `/b/{token}`.

| # | Route | Fungsi |
|---|---|---|
| P1 | `/` | **Selamat datang / sambung.** Nama masjid, bar progres keseluruhan, senarai 10 langkah dengan status (✓/separa/kosong), butang "Sambung di Langkah N". Jika status ≥ `approved` → redirect P10 |
| P2 | `/langkah/{0-9}` | **Wizard** (komponen Livewire `WizardStep`). Navigasi: Kembali / Simpan & Keluar / Seterusnya. **Autosave:** debounce 800ms + setiap navigasi → `project_sections`. Indikator "Disimpan ✓ HH:MM". Validasi inline BM. Langkah bergantung pilihan (§6) |
| P3 | `/semak` | **Semakan.** Kad ringkasan per langkah + pautan "Edit", **skor kelengkapan** (§6.12) dengan senarai medan wajib yang belum diisi (klik → terus ke medan), butang **Hantar** (aktif @100%). Selepas hantar: banner "Telah dihantar — anda masih boleh edit sehingga draf diluluskan" |
| P4 | `/jana` | **Hab penjanaan.** Kad kuota ("Jana AI: X/3 digunakan · Render reka bentuk: Y/5"), cooldown countdown jika aktif, butang **Jana Draf** (dilumpuhkan + sebab jika gate/kunci/kuota/cooldown gagal). Semasa berjalan: 4 peringkat progres (Livewire poll 3s), elapsed timer, nota "Anda boleh tutup halaman ini — kami akan WhatsApp bila siap." >5 minit: mesej lanjutan tenang. Senarai draf terdahulu (versi, masa, pautan) |
| P5 | `/draf/{generation}` | **Pemapar draf.** iframe `sandbox=""` memuatkan P6, toolbar: togol Mobile/Desktop (lebar iframe 390px/100%), label versi & masa. Bar tindakan: **✓ Luluskan** · **Tweak Reka Bentuk (percuma)** · **Tweak Kandungan (AI — baki X)** · **Hantar Nota**. Banner kekal atas: "Ini DRAF SAMPEL untuk semakan — laman sebenar akan dibina selepas kelulusan" |
| P6 | `/draf/{generation}/penuh` | HTML draf mentah (snapshot). **Header:** `Content-Security-Policy: default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'`, `X-Robots-Tag: noindex` |
| P7 | `/tweak/reka` | GET+POST. Pemilih pakej/warna/font/ikon (komponen sama §7.5) → POST: render semula serta-merta (tiada AI), `quota_design_used++` (had 5), cipta `generations` jenis `design_render`, redirect P5 versi baru |
| P8 | `/tweak/kandungan` | GET+POST. **Borang tweak berstruktur:** checkbox kategori [Nada penulisan · Tajuk hero · Perenggan tentang · Ringkasan perkhidmatan · Ringkasan fasiliti · Lain-lain] + `message*` (max 600, "Terangkan dengan jelas apa yang perlu diubah"). POST → semakan kuota/cooldown/kunci → `GenerateDraftJob` jenis `content_tweak` (prompt = data + JSON semasa + arahan tweak) → P4 |
| P9 | `/lulus` | GET+POST. Ringkasan draf yang diluluskan + **borang perakuan:** `pic_name*`, `pic_position*` (cth "Setiausaha AJK"), `pic_phone*`, checkbox **kuasa** ("Saya mengesahkan saya diberi kuasa oleh AJK masjid…") + checkbox **PDPA/ketepatan** (§12.4). POST → modal pengesahan "Tindakan ini muktamad" → rekod `approvals` (snapshot beku), status `approved`, notifikasi admin, redirect P10 |
| P10 | `/status` | **Penjejak.** Timeline status (Diluluskan → Pakej Dieksport → Dalam Pembinaan → Semakan → Live) + **thread nota dua hala** (PIC ↔ admin), borang nota baharu |
| P11 | `POST /nota` | Simpan `notes` (author=pic) + notifikasi admin |

**Selepas `approved`:** P2/P3/P7/P8 menjadi baca-sahaja (banner "Draf telah diluluskan — hubungi kami untuk perubahan").

## 5.3 Admin — Filament `/admin` (auth + 2FA wajib)

**Dashboard widgets:** Corong (Lead baru / Dijemput / Sedang isi / Dihantar / Draf / Lulus — nombor + klik ke senarai), **Kos AI bulan ini** (RM + token, dari `generations`), Queue health (job pending/failed), Projek aktif terkini, Token hampir luput (≤5 hari).

**Resources:**

| Resource | Table columns | Actions penting |
|---|---|---|
| `LeadResource` | masjid, negeri, PIC, telefon, status(new/contacted/qualified/rejected), tarikh | **Layakkan & Jemput** (borang: emel PIC, tempoh token [lalai 30 hari], kuota AI [lalai 3] → cipta project+invitation → hantar notifikasi) · Tolak (+sebab) · Tanda dihubungi |
| `ProjectResource` | masjid, tier, negeri, zon, status (badge), kuota X/N, kos RM, last_active | View page dengan **tab:** Ringkasan · Jawapan Wizard (paparan baca semua `project_sections`, dikumpul ikut langkah) · Aset (galeri) · Penjanaan (senarai + kos + butang lihat draf) · Tweak & Nota (thread; balas di sini) · Kelulusan (snapshot) · Audit. **Header actions:** Top-up kuota AI (+N, log) · Tukar status (dropdown transitions sah sahaja) · **Eksport Pakej Serahan** (hanya `approved+`; jana ZIP §14, muat turun) · Buka wizard sebagai PIC (guna token — untuk bantuan telefon) |
| `InvitationResource` | projek, PIC, luput, dibuka?, aktif terakhir | Hantar semula · Lanjut tempoh (+N hari) · **Batalkan** (revoke serta-merta) · Salin pautan |
| `GenerationResource` (baca) | projek, jenis, status, model, token in/out, kos, masa | Lihat input/output JSON · Lihat draf · **Cuba semula** (failed sahaja; tidak menyentuh kuota) |
| `AiProviderResource` | nama, driver, model, aktif, default | Borang: name, driver(anthropic/openai_compatible), base_url, api_key(**encrypted cast**, write-only — paparan `••••` + 4 aksara akhir), model, max_tokens(lalai 3000), temperature(0.7), timeout(90s), is_active, is_default(satu sahaja). **Action "Uji Sambungan":** panggilan mini "Balas: OK" → papar hasil/ralat |
| `DesignPackageResource` | 5 pakej seed §7 | Edit tokens (JSON editor) · pratonton |
| `VerseLibraryResource` | ayat, sumber, aktif | §9.2 — admin sahaja boleh tambah; medan `verified_by` wajib |
| `NoteResource`, `NotificationLogResource`, `AuditLogResource`, `JakimZoneResource` | baca/urus | Zon: action **"Sahkan dengan e-Solat"** (jalankan semakan §16.A satu-satu, papar hasil) |

**Settings page (Filament custom page):** `whatsapp_gateway_url`, `whatsapp_gateway_secret` (encrypted), `gen_cooldown_minutes` (lalai 5), `default_ai_quota` (3), `default_design_quota` (5), `invitation_default_days` (30), `admin_notify_email`.

---

# §6 — WIZARD: SPESIFIKASI PENUH SETIAP LANGKAH & MEDAN

**Konvensyen jadual:** `*` = wajib untuk gate. **Peta** = kunci `spec.json` (§14.2) ⇢ destinasi (skema Sanity mamkl / variable build-brief). Semua label & help text UI dalam BM; contoh WAJIB dipaparkan sebagai placeholder/hint (corak "Cth: …" — mengikut amalan skema Sanity sedia ada Azan). Setiap langkah = satu halaman; Kembali sentiasa ada; data tidak hilang.

## Langkah 0 — Jenis Masjid & Titik Mula

| Medan | Jenis | Wajib | Butiran | Peta |
|---|---|---|---|---|
| `tier` | radio kad (3) | * | **surau_ringkas** "Laman padat 5–7 halaman — waktu solat, aktiviti, infaq, hubungi" · **masjid_kariah** "Laman komuniti penuh — kelas, khidmat kariah, galeri (spt mamkl.my)" · **masjid_besar** "Laman korporat + pelawat — organisasi penuh, tempahan, dwibahasa (spt masjidwilayah.gov.my)". Setiap kad: ikon + 1 ayat + anggaran bilangan halaman | `meta.tier` ⇢ pemilihan preset |
| `is_gov` | toggle | * (lalai false) | "Masjid kerajaan / akan guna domain .gov.my?" — help: "Jika Ya: pek pematuhan (Privasi, Keselamatan, Piagam, Hakcipta), dwibahasa & maklumat korporat akan dihidupkan secara automatik" | `meta.is_gov` ⇢ pek pematuhan build-brief |

**Tindakan selepas simpan Langkah 0:** apply **matriks preset** (§6.11) ke `project_pages` — HANYA jika Langkah 3 belum pernah disentuh (jangan tulis-ganti pilihan manual).

## Langkah 1 — Maklumat Asas Masjid

| Medan | Jenis | Wajib | Validasi / Butiran | Peta |
|---|---|---|---|---|
| `official_name` | text | * | max 150. Cth: "Masjid Al-Muttaqin Wangsa Melawati" | `mosque.official_name` ⇢ siteSettings, metadata title |
| `short_name` | text | | max 40. Cth: "MAM" | `mosque.short_name` ⇢ header logo text |
| `address_line1`* / `address_line2` / `postcode`* / `city`* | text | * | poskod regex `^[0-9]{5}$` | `mosque.address.*` ⇢ siteSettings.contact, JSON-LD |
| `state` | select 16 negeri | * | Menapis cadangan zon | `mosque.state` |
| `jakim_zone` | select boleh-cari | * | Sumber: table `jakim_zones` (§16.A), ditapis ikut `state`, format paparan "WLY01 — Kuala Lumpur, Putrajaya". Help: "Zon menentukan waktu solat rasmi JAKIM di laman anda" | `mosque.jakim_zone` ⇢ siteSettings.prayerZone `[KRITIKAL — §9.3]` |
| `authority` | text + datalist | * | Datalist cadangan: MAIWP, JAWI, JAIS, MAIS, JAIJ, MAIJ, JAIM, MAIM, JAIPk, JAIPP, MAINPP, MUIP, MAIK, MAIDAM, MAINS, MAIPs `[DISAHKAN: MAINS=N9, MAIDAM=Terengganu; P. Pinang ada DUA entiti sah — JAIPP (jabatan) & MAINPP (majlis). Senarai cadangan sahaja — teks bebas dibenarkan kerana penamaan berbeza ikut negeri]` | `mosque.authority` |
| `established_year` | number | | 1800–2026 | `mosque.established_year` ⇢ stat "Ditubuhkan" |
| `capacity` | number | | jemaah. Cth: 1500 | `mosque.capacity` ⇢ stat |
| `gps` | text gabungan → parse lat,lng | * | Terima "3.1985, 101.7308". Help berlangkah: "Buka Google Maps → tekan lama pada masjid → salin koordinat". Validasi julat Malaysia: lat 0.8–7.5, lng 99.5–119.5 | `mosque.gps` ⇢ JSON-LD Geo, peta, kiblat |
| `google_maps_url` | url | | | `mosque.maps_url` |
| `phone_primary`* / `phone_secondary` | tel | * | E.164/01x | `mosque.phones` ⇢ siteSettings.contact |
| `email` | email | * | | `mosque.email` |
| `facebook_url` / `instagram_url` / `youtube_url` / `tiktok_url` | url | | FB penting (import masa depan) | `mosque.socials` |
| `logo_status` | radio | * | `ada` (→ upload wajib) · `perlu_direka` "Perlu direka (kos tambahan — akan dibincang)" · `teks_sahaja` "Guna nama masjid bergaya sebagai logo" | `assets.logo_status` |
| `logo_file` | upload | * jika `ada` | png/svg/jpg, ≤4MB, min 512px sisi pendek; §11.4 | `assets.logo` |

## Langkah 2 — Identiti & Reka Bentuk (pemilih visual)

Susun atur: kiri = pilihan, kanan = **mini-pratonton hidup** (§7.5) yang mengemas kini serta-merta.

| Medan | Jenis | Wajib | Butiran | Peta |
|---|---|---|---|---|
| `design_package` | 5 kad besar | * | §7.2 — setiap kad: nama, swatch 3 warna, contoh tajuk dalam font display, "Sesuai untuk: …". Memilih pakej menetapkan lalai 4 lapisan di bawah | `design.package` |
| `palette` | swatch 5 | (auto) | Boleh diasingkan dari pakej | `design.tokens.*` ⇢ tailwind config build-brief |
| `font_pair` | radio 4 + Arab | (auto) | §7.4 — pratonton "Masjid Al-Hidayah" + aksara Arab sampel dari verse_library | `design.fonts` |
| `icon_style` | 2 dimensi | (auto) | **Berat garisan:** halus 1.25 / sederhana 1.75 / tebal 2.25 · **Bekas:** bulat-penuh / bulat-cair / kotak-lembut / tanpa-bekas. Pratonton: 6 ikon sama (§7.3) dalam setiap gaya — perbandingan adil | `design.icon_style` |
| `layout_home` | radio 4 ilustrasi | (auto) | hero-tengah · hero-belah (teks kiri, imej kanan) · grid-kad · klasik-formal | `design.layout` |
| `islamic_elements` | checkbox | | corak_geometri (latar seksyen) · pembatas_arabesque (divider) · ~~khat khas~~ → papar sebagai "Khat/kaligrafi khas — rekaan tambahan, akan dibincang" (flag sahaja, bukan janji auto) | `design.islamic_elements` |
| `mood` | radio 3 | * | tenang_khusyuk · mesra_keluarga · megah_berwibawa — help: "Ini menentukan nada penulisan draf" | `design.mood` ⇢ prompt AI §8.3 |

## Langkah 3 — Struktur Halaman

Checklist dikumpul ikut 8 kluster (taksonomi disahkan daripada mamkl + mtajbj + masjidwilayah + KLCC + Masjid Negara). Setiap item: nama + tooltip "?" (1 ayat apa isinya + "Dilihat di: …"). Preset tier telah pra-tanda (§6.11); PIC bebas ubah. Kaunter hidup: "Anggaran: N halaman".

**Katalog `page_key` penuh (28):**
`utama` (kekal wajib, tidak boleh nyah-tanda) · **Korporat:** `sejarah`, `perutusan`, `visi_misi`, `ajk`, `direktori_pegawai` · **Ibadah:** `waktu_solat` (wajib-lalai semua tier), `khutbah_jumaat`, `live_streaming`, `kiblat` · **Ilmu:** `kuliah_mingguan`, `kuliah_bulanan_poster`, `kelas_quran`, `kafa` · **Aktiviti:** `berita`, `pengumuman`, `program_akan_datang`, `galeri` · **Kariah:** `nikah`, `jenazah`, `tahlil_doa`, `khidmat_nasihat`, `khairat`, `daftar_kariah_link` · **Fasiliti:** `fasiliti`, `sewa_dewan`, `info_pelawat` · **Kewangan:** `infaq` · **Sokongan:** `soalan_lazim`, `hubungi` (kekal wajib) · **Muat turun:** `muat_turun` · + `custom` repeater (nama + tujuan, max 3).

## Langkah 4 — Kandungan Halaman (borang bersyarat — enjin sub-borang)

Dipaparkan sebagai **akordion**: satu panel bagi setiap `page_key` yang ditanda di Langkah 3 dan memerlukan input. Panel yang tidak ditanda TIDAK dipaparkan. Setiap panel ada status ✓/kosong. Medan `[AI]` = jika PIC pilih mod "Butir ringkas", AI akan mengarang semasa penjanaan draf (§8.3) dan hasilnya dibenderakan "Dijana AI — sila semak".

| Panel | Medan & peraturan | Peta |
|---|---|---|
| `sejarah` | `mode`* radio: tulis_penuh (textarea ≤3000) / butir_ringkas `[AI]` (repeater bullet ≤10, cth "Ditubuhkan 1987 oleh penduduk kampung…") / kemudian ("akan diberi selepas kelulusan") · `milestones` repeater{tahun, peristiwa} · `former_leaders` repeater{nama, tempoh} | `content.sejarah` ⇢ historyArticle, historicalLeader |
| `perutusan` | `role`* select(Nazir/Imam Besar/Pengerusi) · `name`* · `photo` upload · `mode`* sama spt sejarah `[AI]` | `content.perutusan` ⇢ pageContent |
| `visi_misi` | `visi`, `misi`, `moto` textarea — setiap satu ada butang "Guna contoh" (3 templat statik boleh-edit; BUKAN AI) | `content.visi_misi` |
| `ajk` | `structure_note` · `members` repeater{`name`*, `position`*, `group` select(pengurusan/wanita/belia), `photo`} · pilihan "Senarai penuh akan dihantar kemudian" (flag) · **Notis PDPA inline:** "Pastikan setiap individu bersetuju nama & gambar dipaparkan" (§12.4) | `content.ajk` ⇢ committee |
| `waktu_solat` | Sahkan zon (papar dari L1, pautan edit) · `show_countdown` toggle(lalai on) · `show_hijri` toggle(on) — **tiada input masa; §9.3** | `features.prayer` |
| `khutbah_jumaat` | `mode`: arkib_pdf / video / teks — + nota penyampai tetap | `content.khutbah` |
| `live_streaming` | `platform`* select(YouTube/Facebook) + `channel_url`* | `features.live` ⇢ live-stream lib |
| `kuliah_mingguan` | repeater{`day`* select, `time`* text "8:30–9:30 malam", `topic`*, `speaker`, `kitab`, `session` select(subuh/dhuha/maghrib/isyak/jumaat)} — min 1 baris jika panel aktif | `content.kuliah[]` ⇢ kuliahSchedule/weeklyKuliahSlot |
| `kelas_quran` | repeater{`name`*, `level`* select **enum dikunci dari skema mamkl:** tahsin/hafazan/tadabbur/dhuha/tajwid/ulum/qiraat, `days`, `time`, `location`, `focus` ≤160, `fee`} | `content.quran_classes[]` ⇢ quranClass (1:1) |
| `nikah`,`jenazah`,`tahlil_doa`,`khidmat_nasihat` (satu templat) | setiap khidmat: `short_desc`* ≤160 · `full_desc` `[AI]` boleh kosong · `requirements` repeater string · `documents` repeater string · `fee` text "RM50 / percuma" · `apply_method`* ≤300 "Hubungi pejabat / walk-in / borang" · `contact_person` | `content.services[]` ⇢ **service (medan 1:1 dengan skema mamkl: name, shortDescription, fullDescription, requirements, documents, fee, applyMethod)** |
| `sewa_dewan` | medan khidmat + `capacity` number · `rates` repeater{pakej, harga} · `catering_panel` toggle + repeater{nama, telefon} ("Senarai Panel Katering" — corak masjidwilayah) | `content.dewan` |
| `khairat` | `monthly_fee` text · `terms` textarea · `form_pdf` upload · `contact`* | `content.khairat` |
| `fasiliti` | checklist 12 (ruang solat utama/wanita, wuduk L/W, dewan, bilik kuliah, perpustakaan, parkir, OKU, lif, wifi, bilik jenazah) + custom repeater; setiap yang ditanda: `desc` ≤160 pilihan `[AI]` | `content.facilities[]` ⇢ facility |
| `infaq` | `categories` repeater{`icon` select **senarai tertutup dari skema mamkl:** HeartHandshake/HandHeart/Building/Users/BookOpen/Sparkles, `title`*, `desc` ≤160} — **pra-isi 4:** Infaq Am, Wakaf, Pembinaan, Anak Yatim (boleh edit/padam) · `bank_name`* · `bank_account`* · `account_holder`* · `qr_image` upload · notis: "Nombor akaun akan dipaparkan awam — sahkan betul" | `content.infaq` ⇢ siteSettings.bankInfo+infaqCategories |
| `berita`/`pengumuman` | `seed_items` repeater{tajuk, tarikh, ringkasan ≤200} max 3 — "Untuk mengisi laman semasa pelancaran" · nota "Kemas kini seterusnya melalui CMS/perkhidmatan" | `content.news_seed[]` ⇢ announcement |
| `galeri` | upload multi ≤12, setiap satu `caption` pilihan · **checkbox wajib jika ada wajah:** "Saya mengesahkan kebenaran individu dalam gambar telah diperoleh (termasuk penjaga bagi kanak-kanak)" | `assets.gallery[]` |
| `soalan_lazim` | repeater{`category` select enum mamkl: umum/pernikahan/jenazah/dewan/kelas/infaq, `q`*, `a`*} + butang "Muatkan 8 soalan lazim biasa" (templat statik boleh-edit) | `content.faq[]` ⇢ faq |
| `info_pelawat` (auto-tanda tier besar) | `visiting_hours` repeater{hari, masa} · `dress_code` textarea (templat lalai disediakan) · `getting_here` textarea (transit) · `tour_available` toggle + `tour_contact` · `english_khutbah` toggle — corak disahkan dari Masjid Negara/Wilayah | `content.visitor` |
| `hubungi` | `office_hours` repeater{hari, masa} · `form_recipient_email`* (lalai = email L1) | `content.hubungi` ⇢ siteSettings.officeHours |
| `muat_turun` | repeater{`title`*, `file` pdf ≤10MB} max 8 | `assets.docs[]` |

## Langkah 5 — Fungsi & Ciri

| Medan | Jenis | Butiran | Peta |
|---|---|---|---|
| `payment_gateway` | radio | **toyyibpay** ("dipakai ramai masjid; akaun mudah") · **billplz** · **duitnow_qr_statik** ("papar QR sahaja — paling ringkas") · **fpx_korporat** ("perlu akaun korporat bank — proses lebih lama") · **manual_bank** ("papar no. akaun sahaja"). + `gateway_status` radio: sudah_ada(+ id akaun) / belum("perlu bantuan daftar") `[Disahkan dari lapangan: mamkl guna DuitNow QR/FPX; KLCC guna ToyyibPay — jangan andaikan satu]` | `features.payment` |
| `whatsapp_button` | toggle + `wa_number` | butang terapung wa.me | `features.wa_button` |
| `whatsapp_channel` | toggle + `channel_url` + `member_label` | corak siteSettings mamkl ("Cth: 1,200+ ahli kariah") | `features.wa_channel` |
| `add_to_calendar` | toggle | butiran ICS utk program (corak mamkl) | `features.ics` |
| `bilingual` | toggle | **auto-ON jika tier besar ATAU is_gov**; help "BM sahaja / BM+English" | `features.i18n` |
| `cms_updater` | radio * | **KRITIKAL — menentukan seni bina laman sebenar:** `ajk_sendiri` "AJK akan kemas kini sendiri → CMS (Sanity) dipasang" · `urus_azan` "Diuruskan penyedia → pakej selenggara" · `jarang` "Jarang berubah → laman statik" | `features.cms` ⇢ build-brief Prompt 8 on/off |
| `kariah_system` | radio | tiada / `pautan_sedia`(+url — cth ssda.mamkl.my) / `perlu_bina`("dicatat — projek berasingan") | `features.kariah` |
| `tv_display`, `pwa`, `wa_broadcast` | toggle flags | Direkod sahaja; papar "Ciri tambahan — akan dibincang" `[BACKLOG]` | `features.flags` |

## Langkah 6 — Media & Aset

| Medan | Butiran |
|---|---|
| `hero_mode`* | radio: upload (1–3 imej landskap, ≥1600px lebar) / `perlu_fotografi` ("Perlu khidmat fotografi — dibincang; tip: cahaya terbaik ±1 jam sebelum Maghrib") / `stok_sementara` ("guna imej stok sehingga gambar sebenar sedia") |
| `hero_files` | wajib jika upload; §11.4 |
| `facility_photos` | upload per fasiliti ditanda (pilihan) |
| `video_url` | pilihan (YouTube) |
| Semua upload | jpg/png/webp ≤8MB; pdf ≤10MB; imej di-*re-encode* + EXIF (termasuk GPS) DIBUANG (§11.4) |

## Langkah 7 — Rujukan & Inspirasi

`liked_refs` repeater{`url`, `what_liked`* ≤200 "Apa yang anda suka pada laman ini?"} max 3 · `dislikes` textarea ≤500 "Apa yang anda TIDAK mahu?" (cth: "terlalu banyak animasi", "warna gelap") · pautan galeri contoh dalam UI: mamkl.my, masjidwilayah.gov.my, mtajbj.gov.my (pautan luar sahaja). Peta: `references.*` ⇢ build-brief nota reka bentuk.

## Langkah 8 — Teknikal & Operasi

| Medan | Butiran | Peta |
|---|---|---|
| `domain_status`* | radio: `ada`(+ `domain_name`, `registrar`, `dns_access` toggle) / `belum`(+ `domain_wishes` repeater 3 cadangan, cth "masjidalfalah.my") / `gov_my`("melalui proses agensi — MYNIC .gov.my perlu permohonan rasmi jabatan; dicatat") | `technical.domain` |
| `existing_site` | url + `migrate_content` toggle | `technical.legacy` |
| `official_email_status` | radio: ada / perlu ("emel @domain — dibincang") | `technical.email` |
| `hosting` | radio: `urus_azan` (lalai) / `sendiri`(+butiran) | `technical.hosting` |
| `maintenance` | radio: pakej_bulanan / sendiri / bincang | `technical.maintenance` |
| `target_live` | date pilihan | `technical.target_date` |

## Langkah 9 — Nota, Perakuan & Persetujuan

| Medan | Wajib | Butiran |
|---|---|---|
| `free_notes` | | textarea ≤2000 "Apa-apa lagi yang anda mahu kami tahu — gaya, ciri, harapan" |
| `budget_hint` | | select pilihan: <RM1k / RM1–3k / RM3–5k / >RM5k / bincang ("membantu kami mencadang pakej sesuai") |
| `pic_name`, `pic_position`, `pic_phone` | * | pra-isi dari invitation; boleh betulkan |
| `consent_pdpa` | * | checkbox + teks penuh §12.4 + pautan `/privasi` |
| `declare_truth_authority` | * | checkbox: maklumat benar + diberi kuasa mewakili masjid |

## 6.11 Matriks Preset Tier (nilai awal `project_pages`)

| page_key | surau_ringkas | masjid_kariah | masjid_besar | +is_gov |
|---|---|---|---|---|
| utama, waktu_solat, hubungi | ✓ | ✓ | ✓ | |
| pengumuman, infaq, soalan_lazim | ✓ | ✓ | ✓ | |
| kuliah_mingguan | ✓ | ✓ | ✓ | |
| sejarah, ajk, fasiliti, galeri, berita | – | ✓ | ✓ | |
| kelas_quran, nikah, jenazah, tahlil_doa, khairat | – | ✓ | ✓ | |
| khutbah_jumaat, program_akan_datang, muat_turun | – | ✓ | ✓ | |
| perutusan, visi_misi, direktori_pegawai | – | – | ✓ | ✓ paksa |
| sewa_dewan, info_pelawat, live_streaming | – | – | ✓ | |
| kiblat, kafa, khidmat_nasihat, daftar_kariah_link, kuliah_bulanan_poster | – | pilihan (tak ditanda) | pilihan | |
| Pek pematuhan (privasi/keselamatan/piagam/hakcipta — halaman statik) | – | – | – | ✓ auto (bukan pilihan) |
| bilingual (L5) | off | off | **on** | **on paksa** |

## 6.12 Skor Kelengkapan & Gate

- **Set medan wajib aktif** = wajib global (L0,L1,L2 mood,L9) + wajib bersyarat setiap panel L4 yang halamannya ditanda + wajib L5 (`payment_gateway` jika `infaq` ditanda; `cms_updater` sentiasa) + L6 (`hero_mode`; fail jika mode=upload) + L8 (`domain_status`).
- `skor = round(100 × wajib_diisi / jumlah_wajib_aktif)`.
- **Gate Hantar (P3):** skor = 100.
- **Gate Jana (P4):** status ≥ `submitted` DAN (logo: `logo_file` wujud ATAU `logo_status ≠ ada`) DAN (hero: fail wujud ATAU mode ≠ upload).
- Senarai "belum lengkap" memaparkan label BM medan + pautan terus (anchor) ke medan tersebut.

## 6.13 Autosave & Sambung

- Livewire `wire:model.blur` + debounce 800ms → simpan JSON langkah ke `project_sections` (upsert `project_id+section_key`).
- Repeater/upload: simpan serta-merta selepas tindakan.
- Navigasi keluar langkah = simpan penuh + validasi *lembut* (ralat dipapar tetapi TIDAK menghalang simpan — hanya gate yang menghalang Hantar).
- P1 mengira status setiap langkah dari kewujudan & kesahan data.

---

# §7 — KATALOG PAKEJ REKA BENTUK

## 7.1 Struktur design tokens (JSON dalam `design_packages.tokens`)

```json
{
  "primary": "#1B5E3F", "primaryDark": "#0F3D27", "accent": "#C9A961",
  "ink": "#1A1A1A", "bg": "#FAF7F2", "bgAlt": "#EFE8DC",
  "radius": "1rem", "headerStyle": "transparent-to-solid"
}
```
**Peraturan kontras (enforce dalam shell):** `accent` (emas) TIDAK boleh menjadi warna teks atas latar cerah (gagal WCAG) — guna untuk pembatas, ikon atas gelap, badge berlatar `primary`. Teks badan = `ink` atas `bg`. Semua `primary` di bawah telah dipilih dengan kontras ≥4.5:1 atas putih.

## 7.2 Lima pakej (seed `design_packages`)

| # | key | Nama | primary / dark / accent | bg / bgAlt / ink | Font (badan/display/Arab) | Layout lalai | Sesuai untuk |
|---|---|---|---|---|---|---|---|
| 1 | `warisan_hijau` | **Warisan Hijau** | #1B5E3F / #0F3D27 / #C9A961 | #FAF7F2 / #EFE8DC / #1A1A1A | Plus Jakarta Sans / Cormorant Garamond / Amiri | hero-tengah | Semua — **token terbukti produksi mamkl.my** |
| 2 | `biru_nilam` | **Biru Nilam** | #1D4E89 / #10315C / #B08D3E | #F7FAFC / #E8EFF5 / #16202B | Inter / Playfair Display / Amiri | hero-belah | Masjid bandar moden |
| 3 | `emas_kubah` | **Emas Kubah** | #8C6D2F / #5C4620 / #1B5E3F | #FBF8F1 / #F1E9D8 / #241D12 | Figtree / Lora / Scheherazade New | klasik-formal | Masjid bersejarah/klasik |
| 4 | `teal_kontemporari` | **Teal Kontemporari** | #0F6E6E / #084C4C / #E0A94F | #F6FBFA / #E3F0EE / #10201F | IBM Plex Sans / IBM Plex Serif / Amiri | grid-kad | Komuniti muda/mesra keluarga |
| 5 | `marun_agung` | **Marun Agung** | #6E1F2E / #4A121D / #C9A961 | #FAF6F4 / #F0E6E4 / #1D1416 | Plus Jakarta Sans / Cormorant Garamond / Amiri | klasik-formal | Masjid besar/kerajaan |

Setiap pakej ada `icon_style` lalai: 1=sederhana+bulat-cair, 2=sederhana+kotak-lembut, 3=halus+tanpa-bekas, 4=tebal+bulat-penuh, 5=sederhana+bulat-penuh.

## 7.3 Set ikon terkurasi (Lucide — 24, nama disahkan; 9 pertama dari senarai tertutup skema mamkl)

`HeartHandshake · HandHeart · Building · Users · BookOpen · Sparkles · Droplets · Car · Heart · Moon · Calendar · Clock · MapPin · Phone · Mail · Landmark · GraduationCap · Baby · Utensils · Mic · Video · Wallet · QrCode · Accessibility`

Pratonton gaya ikon (L2) memaparkan 6 yang sama (Building, BookOpen, HeartHandshake, Users, Calendar, MapPin) dalam setiap kombinasi berat/bekas. **Wizard & draf** guna lucide static SVG (pakej npm `lucide-static` `[DISAHKAN wujud]` — sisip SVG TERPILIH terus dalam Blade, tiada JS runtime; amaran lucide bahawa pakej static "untuk prototyping" merujuk kes bundling JS penuh dan TIDAK relevan untuk sisipan server-side terpilih begini).

## 7.4 Pasangan font (semua Google Fonts; Arab WAJIB Amiri atau Scheherazade New — kedua-duanya sokongan Arab penuh; Amiri = standard produksi mamkl)

A: Plus Jakarta Sans + Cormorant Garamond · B: Inter + Playfair Display · C: Figtree + Lora · D: IBM Plex Sans + IBM Plex Serif. Arab: Amiri (lalai) / Scheherazade New. Draf memuatkan font via Google Fonts `<link>` (pengecualian CSP dibenarkan khusus `fonts.googleapis.com`/`fonts.gstatic.com` pada route draf).

## 7.5 Mini-pratonton hidup (L2 & P7)

Komponen Blade/Alpine `x-design-preview`: menerima tokens sebagai CSS custom properties, merender (1) header masjid dengan nama sebenar dari L1 + nav dummy, (2) kad waktu solat contoh (label statik "Maghrib 19:29" — data hiasan, BUKAN dari API), (3) satu kad khidmat dengan ikon mengikut gaya pilihan, (4) butang primary+accent. Kemas kini reaktif tanpa muat semula. **Data pratonton = kandungan sebenar PIC di mana ada** — inilah yang menghapuskan "blur".

---

# §8 — MODUL AI & PENJANAAN DRAF

## 8.1 Abstraksi provider — `app/Services/Ai/`

```php
interface AiClient {
    /** @return AiResult{content: string, tokensIn: int, tokensOut: int} @throws AiException */
    public function complete(string $system, string $user, AiProvider $cfg): AiResult;
}
```
- **AnthropicClient:** `POST {base_url|https://api.anthropic.com}/v1/messages`, headers `x-api-key`, `anthropic-version: 2023-06-01`; body `{model, max_tokens, system, messages:[{role:user,content}]}`; baca `content[0].text`, `usage.input_tokens/output_tokens`.
- **OpenAiCompatibleClient:** `POST {base_url}/chat/completions`, header `Authorization: Bearer`; body `{model, max_tokens, temperature, messages:[{system},{user}], response_format:{type:"json_object"} }` — jika provider menolak `response_format` (sesetengah endpoint serasi-OpenAI), tangkap ralat & ulang TANPA medan itu. `[DISAHKAN: OpenAI juga menolak json_object jika perkataan "json" TIADA dalam mesej — system prompt §8.3 memang mengandungi "JSON"; JANGAN buang perkataan itu]` Baca `choices[0].message.content`, `usage.*`. Meliputi: OpenAI, GLM (Zhipu endpoint serasi), OpenRouter, **Ollama** (`base_url=http://host:11434/v1`, `api_key="ollama"`).
- **Contoh nilai `model` semasa (Julai 2026 — rujukan placeholder sahaja; nilai sebenar diisi admin, JANGAN hard-code):** Anthropic: `claude-sonnet-5` (disyor utama), `claude-haiku-4-5` (ekonomi), `claude-opus-4-8` (premium); GLM/Zhipu: `glm-5`, `glm-4.7`; Ollama: mana-mana model tempatan terpasang.
- Timeout dari `cfg->timeout_s` (lalai 90); TIADA retry di lapisan client (retry di Job).

## 8.2 Kontrak JSON kandungan draf (output AI — WAJIB tepat)

```json
{
  "meta":   { "title": "≤60", "description": "≤160" },
  "hero":   { "eyebrow": "≤40", "headline": "≤60", "subheadline": "≤140",
              "cta_primary_label": "≤20", "cta_secondary_label": "≤20" },
  "about":  { "heading": "≤60", "paragraphs": ["60–120 patah, 2–3 item"],
              "stats": [ { "label": "≤20", "value": "≤12" } ] },
  "services": [ { "key": "dari input", "title": "≤40", "blurb": "≤160" } ],
  "facilities": [ { "title": "≤40", "blurb": "≤140" } ],
  "kuliah": { "heading": "≤60", "intro": "≤200" },
  "infaq":  { "heading": "≤60", "paragraph": "≤240" },
  "announcements": [ { "title": "≤70", "date_label": "≤20", "excerpt": "≤140" } ],
  "visitor_info": { "heading": "≤60", "paragraph": "≤240" },
  "footer_description": "≤200"
}
```
Kunci pilihan (`visitor_info`, `announcements`…) hanya jika halaman berkaitan ditanda — senarai kunci yang diminta dibina dinamik oleh PromptBuilder.

## 8.3 PROMPT (verbatim — simpan di `resources/prompts/draft-system.txt` & `draft-user.blade.php`)

**SYSTEM:**
```
Anda penulis kandungan laman web masjid di Malaysia. Hasilkan kandungan Bahasa
Melayu baku yang mesra, hormat dan jelas, BERDASARKAN HANYA data yang diberikan.

PERATURAN MUTLAK:
1. Balas JSON SAHAJA, tepat mengikut skema & had panjang yang diberi. Tiada
   markdown, tiada teks luar JSON.
2. DILARANG menulis sebarang aksara Arab, ayat Al-Quran, hadith, atau doa dalam
   tulisan Arab. Sistem menolak automatik output yang mengandungi aksara Arab.
   Rujukan umum dalam rumi (cth "sebagaimana anjuran Islam") dibenarkan.
3. DILARANG mereka fakta: tahun, angka, nama orang, alamat, atau sejarah yang
   TIADA dalam data. Jika maklumat tiada, tulis ayat umum tanpa angka/nama.
4. Nada penulisan: {{MOOD}}.
5. Gunakan nama masjid sebagaimana diberi. Jangan gelar masjid dengan pangkat
   yang tidak diberikan (cth jangan tulis "masjid negeri" jika tidak dinyatakan).
6. Elakkan superlatif kosong ("terbaik di Malaysia") dan janji ("kami sentiasa
   24 jam") yang tiada dalam data.
```
`{{MOOD}}` ⇢ tenang_khusyuk→"tenang, khusyuk, merendah"; mesra_keluarga→"mesra, hangat, komuniti"; megah_berwibawa→"formal, berwibawa, meyakinkan".

**USER (Blade template):** blok `DATA MASJID` (JSON dipilih & **diminimumkan PII** — §12.7: masukkan nama masjid, alamat bandar/negeri, tahun, kapasiti, senarai khidmat/fasiliti/kelas dari L4, mood; **JANGAN masukkan**: telefon/emel individu, no. akaun bank penuh, nama+telefon PIC, IC) + blok `SKEMA OUTPUT` (§8.2 dengan hanya kunci diperlukan) + blok `ARAHAN TWEAK` (jenis `content_tweak` sahaja: JSON semasa + kategori + mesej PIC + "ubah HANYA bahagian berkaitan, kekalkan yang lain").

## 8.4 Validasi output (selepas setiap panggilan — `DraftContentValidator`)

Urutan: (1) strip fence ```json jika ada → `json_decode` strict; (2) **reject jika mengandungi aksara julat `\x{0600}-\x{06FF}`, `\x{0750}-\x{077F}`, `\x{08A0}-\x{08FF}`, `\x{FB50}-\x{FDFF}`, `\x{FE70}-\x{FEFF}`** (Arab & bentuk persembahan — penguatkuasaan mekanikal §9.1); (3) semak kunci wajib wujud & tiada kunci asing; (4) had panjang (potong lembut +10% toleransi, gagal jika >25%); (5) semak `services[].key ⊆` kunci input. **Gagal mana-mana → retry penuh (kiraan retry Job), bukan cuba "baiki".** Semua kegagalan dilog dengan sebab.

## 8.5 Rendering draf

`DraftRenderer::render(Project $p, array $content, array $tokens): string` → Blade `draft/shell.blade.php`:
- HTML lengkap-kendiri; `<style>` tunggal (CSS tulen ±400 baris, CSS variables dari tokens; TIADA Tailwind pada draf — sifar pipeline build untuk draf).
- Seksyen dirender ikut `project_pages` (utama sahaja — draf = **halaman utama** yang mewakili keseluruhan; senarai halaman lain dipapar sebagai nav + seksyen "Halaman penuh laman anda" bergrid).
- **Waktu solat dalam draf = blok statik berlabel "Contoh paparan — waktu sebenar akan diambil terus dari JAKIM e-Solat (zon {ZON})"** — TIADA panggilan API semasa jana (§9.3).
- Ayat Arab hiasan (hero) = SATU entri aktif dari `verse_library` (§9.2), dirender server, font Arab pilihan, `dir="rtl"`.
- **Suntikan server (bukan pilihan):** banner tetap atas "DRAF SAMPEL — BUKAN LAMAN SEBENAR · Dijana {tarikh} · Versi {n}" + watermark pepenjuru CSS berulang "DRAF" opacity 0.05 + `<meta name="robots" content="noindex">`.
- Imej: hero/logo/galeri dari storage app (URL mutlak); fallback placeholder kelabu berlabel jika tiada.
- Kandungan mod `[AI]` dibalut penanda halus "✎ Dijana AI — sila semak ketepatan" (tooltip).
- Output disimpan `storage/app/drafts/{project}/{generation}.html` (snapshot kekal; tweak = fail baharu).

## 8.6 `GenerateDraftJob` (queue `ai`, `$tries=1` — retry manual dalam handle)

```
1. TX: lock project → semak gate + kuota + cooldown + tiada generation aktif
   → cipta generations{queued} (jika gagal semakan: batal senyap + log)
2. status=processing, progress_step=1 → bina prompt (input_snapshot disimpan)
3. step=2 → AiClient::complete()  [percubaan 1..3: gagal → tidur 30s, 90s]
4. step=3 → DraftContentValidator (gagal kira sebagai percubaan juga)
5. step=4 → DraftRenderer → simpan path, tokens, kos (§8.8)
6. succeeded → jika type∈{initial,content_tweak}: quota_ai_used++ (SELEPAS
   berjaya sahaja) → status projek → notifikasi PIC (WhatsApp+mail)
7. Semua percubaan gagal → failed + error → kuota TIDAK disentuh → mail admin
```
`$timeout=300`; `failed()` melengkapkan langkah 7 jika worker mati. **Cooldown:** jana AI seterusnya dibenarkan `settings.gen_cooldown_minutes` selepas `finished_at` terakhir jenis AI.

## 8.7 Jadual kuota (muktamad)

| Tindakan | AI? | Kuota | Had | Nota |
|---|---|---|---|---|
| Jana pertama | ✓ | `quota_ai_used` 1/3 | gate §6.12 | |
| Tweak kandungan | ✓ | 2 lagi (jumlah 3) | cooldown 5 min | borang berstruktur P8 |
| Tweak reka bentuk | ✗ | `quota_design_used` ≤5 | serta-merta | render semula sahaja |
| Gagal/timeout | — | **tidak dikira** | auto-retry 2× | pulangan automatik |
| Top-up | — | admin +N | log audit | selepas nota PIC |

## 8.8 Kos & ledger

`cost_estimate = tokensIn×rate_in + tokensOut×rate_out` — kadar per provider disimpan dalam `ai_providers.meta` (JSON `{rate_in_per_mtok, rate_out_per_mtok, currency}`), **diisi manual oleh admin dari harga semasa provider** `[JANGAN hard-code harga — berubah]`. Anggaran saiz panggilan: input 3–6k token, output 1.5–4k. Dashboard: jumlah bulan, purata/projek, 5 projek termahal.

**Contoh anggaran harga (Julai 2026 — ANGGARAN; harga kerap berubah, sahkan halaman harga rasmi provider):** kelas Sonnet ±USD3 masuk / USD15 keluar per juta token (Sonnet 5: harga pengenalan USD2/USD10 sehingga 31 Ogos 2026); Haiku 4.5 ±USD1/USD5; kelas GPT-4o/4.1 ±USD2.50–5 / USD10–15; GLM flagship jauh lebih murah (±80–90% di bawah kelas Opus). Dengan kontrak JSON §8.2, satu jana ≈ USD0.03–0.10 (kelas Sonnet) ≈ **RM0.15–0.50/jana**.

---

# §9 — GUARDRAIL AGAMA (SYARAT PRODUK — bukan pilihan)

## 9.1 AI DILARANG menghasilkan teks Arab / ayat / hadith
Dikuatkuasakan TIGA lapis: (a) arahan sistem §8.3, (b) **penolakan mekanikal regex aksara Arab §8.4** — walaupun AI "berniat baik", output ditolak, (c) semua kandungan Arab pada draf datang HANYA dari `verse_library` yang direkod manusia. Rasional: ayat Al-Quran yang salah/halusinasi pada laman masjid = kerosakan kredibiliti yang tidak boleh diterima.

## 9.2 `verse_library`
Seed MVP: **SATU** entri — ayat yang telah berada dalam produksi mamkl.my (Surah At-Taubah: 18, teks Arab + terjemahan "Hanyalah yang memakmurkan masjid-masjid Allah ialah orang yang beriman kepada Allah dan Hari Akhirat…" + label sumber). `[KEPUTUSAN SEDAR: spek ini TIDAK menyalin teks ayat tambahan dari ingatan — Azan menambah entri seterusnya secara manual dari mushaf/sumber muktamad, medan verified_by wajib diisi]`. Claude Code: salin teks Arab entri seed TEPAT dari `docs/build-prompts.md` repo mamkl (PROMPT 3) — jangan taip semula dari ingatan.

## 9.3 Waktu solat = JAKIM e-Solat SAHAJA `[DISAHKAN LIVE 7 Julai 2026]`
- Endpoint: `GET https://www.e-solat.gov.my/index.php?r=esolatApi/takwimsolat&period=today&zone={KOD}`
- Response disahkan: `{ prayerTime: [{ hijri, date, day, imsak, fajr, syuruk, dhuha, dhuhr, asr, maghrib, isha }], status:"OK!", serverTime, periodType, lang:"ms_my", zone, bearing }` — masa format `HH:MM:SS`; **medan `dhuha` hadir dalam respons live 7 Julai 2026 tetapi TIADA dalam dokumentasi lama API — parser MESTI melayan `dhuha` sebagai medan PILIHAN (jangan gagal jika ia tiada)**; **`bearing` = arah kiblat zon** (guna untuk halaman kiblat laman sebenar).
- `period=month` untuk cache bulanan; `duration` memerlukan POST form-data `datestart/dateend`.
- **Sistem REKA sendiri TIDAK memanggil API ini semasa jana draf** (draf = blok contoh berlabel). API digunakan HANYA oleh: (a) command `zones:verify` (§16.A), (b) laman sebenar (arahan build-brief: Server Component + cache/revalidate ≥6 jam + fallback pautan e-solat.gov.my — corak sedia terbukti mamkl; JANGAN panggil setiap tick countdown).
- AI TIDAK PERNAH menjana/menganggar waktu solat — tiada laluan kod yang membenarkannya.

## 9.4 Penandaan kandungan AI
Setiap medan mod `[AI]` dibendera dalam spec.json (`"origin":"ai"`) + penanda visual pada draf + baris dalam build-brief: "Kandungan berikut dijana AI dan TELAH/BELUM disemak PIC" — supaya Azan tahu apa perlu semakan manusia sebelum live.

---

# §10 — MODEL DATA (20 jadual)

Konvensyen: semua jadual ada `id` (ULID), `created_at/updated_at`. FK = `constrained()->cascadeOnDelete()` kecuali dinyatakan. Kolum JSON = `json` cast array.

| Jadual | Kolum (jenis — nota) |
|---|---|
| `users` | name, email uniq, password, two_factor_* (Filament/Fortify) — admin sahaja |
| `leads` | mosque_name, state, pic_name, pic_phone, pic_email null, current_website null, notes null, status enum(new,contacted,qualified,rejected) idx, rejected_reason null, project_id null FK nullOnDelete |
| `projects` | lead_id null FK nullOnDelete, mosque_name, short_name null, tier enum(surau_ringkas,masjid_kariah,masjid_besar), is_gov bool, state, jakim_zone(5) idx, **status** enum §4.2 idx, quota_ai_total tinyint=3, quota_ai_used=0, quota_design_used=0, submitted_at/approved_at null, softDeletes |
| `invitations` | project_id FK, **token_hash char(64) uniq**, pic_name, pic_phone, pic_email null, expires_at idx, revoked_at null, opened_at null, last_active_at null, opens_count int=0 |
| `project_sections` | project_id FK, section_key(20) — uniq[project_id,section_key], data json, completed_at null |
| `project_pages` | project_id FK, page_key(40) — uniq[project_id,page_key], enabled bool, custom_name null, sort smallint |
| `assets` | project_id FK, kind enum(logo,hero,gallery,qr,doc,committee_photo,facility_photo,perutusan_photo), path, original_name, mime, size int, width/height null, caption null, meta json null, sort |
| `design_packages` | key uniq, name, tokens json, fonts json, layout, icon_style json, preview_meta json null, is_active bool |
| `project_design` | project_id FK uniq, package_key, overrides json null — {palette?, fonts?, icon_style?, layout?, islamic_elements?, mood} |
| `ai_providers` | name, driver enum(anthropic,openai_compatible), base_url null, **api_key text encrypted cast**, model, max_tokens int=3000, temperature dec(2,1)=0.7, timeout_s int=90, meta json null (kadar §8.8), is_active bool, is_default bool |
| `generations` | project_id FK idx, ai_provider_id null FK nullOnDelete, type enum(initial,content_tweak,design_render), status enum(queued,processing,succeeded,failed) idx, progress_step tinyint=0, input_snapshot json null, output_json json null, rendered_path null, error text null, tokens_in/tokens_out int=0, cost_estimate dec(8,4)=0, attempt tinyint=0, created_by enum(pic,admin), started_at/finished_at null |
| `tweak_requests` | project_id FK, base_generation_id FK generations, categories json, message text, result_generation_id null FK, status enum(pending,applied,failed) |
| `approvals` | project_id FK uniq, generation_id FK, **snapshot json** (spec penuh beku §14.2 + draft path + hash), pic_name, pic_position, pic_phone, consent_pdpa bool, declare_authority bool, ip(45), user_agent, approved_at |
| `handover_exports` | project_id FK, approval_id FK, zip_path, manifest json, exported_at |
| `notes` | project_id FK idx, author enum(admin,pic), author_name, kind enum(general,quota_request,build_update), body text, read_at null |
| `notification_logs` | project_id null FK, event(60), channel enum(mail,whatsapp), recipient, payload json, status enum(sent,failed), error null, sent_at |
| `verse_library` | arabic_text text, translation_bm text, source_label, verified_by, is_active bool |
| `jakim_zones` | code(5) uniq, state idx, districts_label, verified_at null |
| `audit_logs` | actor_type enum(admin,pic,system), actor_id null, action(80) idx, subject_type/subject_id null, meta json null, ip null |
| `settings` | key uniq, value text, is_encrypted bool |
+ jadual Laravel standard: `jobs`, `failed_jobs`, `cache`, `sessions`.

**Peristiwa audit minimum:** invitation.created/sent/revoked/extended, project.status_changed, generation.requested/succeeded/failed/refunded, quota.topup, approval.recorded, handover.exported, note.created, provider.updated, admin.login.

---

# §11 — KESELAMATAN (pengajaran langsung dari code-review mamkl dimasukkan)

## 11.1 Token PIC
Jana: `Str::random(40)`; simpan **SHA-256 sahaja** (plaintext hanya dalam URL yang dihantar). Luput lalai 30 hari (dilanjut = tarikh baharu, token sama). Revoke = serta-merta. **Risiko forward-link diterima secara sedar** (PIC memang digalak kongsi kepada AJK); mitigasi: tiada data sensitif dipapar semula penuh (no. akaun bank di-mask `••••1234` pada paparan semak; nilai penuh hanya dalam spec.json admin), semua tindakan penting direkod IP+UA, admin boleh revoke bila-bila, dan tindakan muktamad (lulus) memerlukan perakuan identiti.

## 11.2 Rate limits (RateLimiter bernama)
| Skop | Had |
|---|---|
| `/minat` POST | 5/min/IP + honeypot |
| Resolusi token (percubaan `/b/*` tidak sah) | 10/min/IP → 429 |
| Autosave | 60/min/token |
| Jana AI POST | 1 serentak/projek (kunci DB) + cooldown + 10/hari/projek (siling keselamatan) |
| Upload | 20/jam/token |
| Nota POST | 10/jam/token |
| Login admin | 5/min (lalai Fortify) + 2FA wajib |

## 11.3 Header & logging `[pengajaran mamkl: header hilang, PII dilog dev-mode, rate limit tiada pada API contact — JANGAN ulang]`
Middleware `SecurityHeaders` (semua respons): `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `X-Frame-Options: DENY` **kecuali** route P6 (`frame-ancestors 'self'` melalui CSP), CSP asas app `default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'` + kelonggaran fonts Google pada P6 sahaja; HSTS di nginx (produksi). **Logging:** helper `mask()` — log TIDAK PERNAH mengandungi telefon/emel/token penuh (`0195•••294`); larangan `Log::debug($request->all())`.

## 11.4 Upload
Whitelist MIME sebenar (finfo, bukan sambungan): imej jpg/png/webp ≤8MB → **re-encode melalui Intervention Image v3** (musnah payload tersembunyi) + **buang EXIF termasuk GPS** (PDPA-baik) `[DISAHKAN: opsyen strip v3 lalai FALSE; dengan driver GD (lalai) metadata memang terbuang semasa re-encode, dengan Imagick strip WAJIB eksplisit — kunci driver GD dalam config, dan ujian EXIF §15.11 menguatkuasakan HASIL tanpa mengira driver]` + hadkan sisi panjang 2400px (hero) / 1200px (lain); pdf ≤10MB, sahkan magic bytes `%PDF`, simpan luar webroot, hidang melalui route bertoken sahaja. Nama fail = ULID (nama asal disimpan dalam DB sahaja).

## 11.5 Lain-lain
Kunci API `encrypted` cast (APP_KEY — jangan rotate tanpa re-encrypt); CSRF semua POST; Filament 2FA wajib (paksa semasa login pertama); backup: `mysqldump` harian + `storage/app` rsync harian, simpan 14 hari (skrip `deploy/backup.sh` — dijana Fasa 9); queue worker user berasingan; `.env` di luar repo.

---

# §12 — PDPA & PERUNDANGAN `[DISAHKAN 7 Julai 2026]`

## 12.1 Rangka yang terpakai
**PDPA 2010 (Akta 709) + Akta Perlindungan Data Peribadi (Pindaan) 2024 [A1727]** — berkuat kuasa berperingkat **1 Jan / 1 Apr / 1 Jun 2025**. Perubahan yang menyentuh REKA: istilah *pengguna data → pengawal data*; **notifikasi pelanggaran data (s.12B, berkuat kuasa 1 Jun 2025)**; kewajipan pemproses data; kuasa penguatkuasaan & penalti dinaikkan (sehingga **RM1 juta / 3 tahun** bagi pelanggaran prinsip teras). Azan/entiti perniagaannya = **pengawal data**; penyedia AI & hosting = **pemproses data**.

## 12.2 Notis Privasi — **WAJIB dwibahasa BM + Inggeris** (keperluan notis s.7 Akta 709)
Kandungan minimum `/privasi`: identiti pengawal data + hubungan; data dikutip (§12.3); tujuan (penyediaan cadangan & pembinaan laman web masjid); **pendedahan kepada pemproses: penyedia perkhidmatan AI & pengehosan, yang mungkin berada di luar Malaysia** (§12.7); tempoh simpanan (§12.8); hak subjek data (akses, pembetulan, tarik balik persetujuan, mudah alih data — hak mudah alih diperkenal Pindaan 2024) + cara memohon; sumber pilihan/wajib medan.

## 12.3 Peta data peribadi dalam sistem
| Data | Lokasi | Sensitiviti |
|---|---|---|
| Nama/telefon/emel PIC | leads, invitations, approvals, L9 | Peribadi — teras |
| Nama, jawatan, **gambar** AJK/imam | L4 ajk/perutusan, assets | Peribadi; gambar = berhati-hati (nota: data biometrik kini "sensitif" di bawah Pindaan 2024 — gambar biasa bukan biometrik, tetapi amal minimum) |
| Gambar jemaah/kanak-kanak (galeri) | assets | **Risiko tinggi** — checkbox kebenaran wajib (§6 L4 galeri) |
| No. akaun bank masjid | L4 infaq | Bukan data peribadi individu, tetapi sensitif operasi — mask pada paparan |
| IP/UA | approvals, audit_logs | Peribadi — minimum, tujuan bukti |

## 12.4 Titik persetujuan (checkbox — teks penuh dalam §16.C)
1. **L9 `consent_pdpa`:** persetujuan pemprosesan bagi tujuan dinyatakan **termasuk pemprosesan kandungan oleh penyedia AI pihak ketiga yang mungkin di luar Malaysia**, rujuk Notis Privasi. 2. **L9 & P9 `declare_truth_authority`:** maklumat benar + diberi kuasa AJK. 3. **Galeri (bersyarat):** kebenaran individu/penjaga diperoleh. Semua rekod checkbox + masa + IP disimpan (approvals/audit).

## 12.5 SOP pelanggaran data (dokumen operasi — jana `docs/SOP-PELANGGARAN-DATA.md` dalam Fasa 9)
Berkuat kuasa 1 Jun 2025: kesedaran insiden → bendung + rekod → nilai "kemudaratan signifikan" (kecederaan fizikal / kerugian kewangan / jejas kredit / dsb.) → **notifikasi Pesuruhjaya PDP secepat praktik, dalam 72 jam dari saat pelanggaran berlaku atau dari saat pengawal data dimaklumkan mengenainya** (pemberitahuan berperingkat dibenarkan; kelewatan mesti didokumen & dijustifikasi) → jika kemudaratan signifikan: **maklum subjek data tanpa kelewatan, ≤7 hari selepas notifikasi Pesuruhjaya**. Kegagalan notifikasi: denda ≤RM250k dan/atau penjara ≤2 tahun. Sistem menyokong: eksport senarai subjek terjejas per projek (query siap dalam SOP).

## 12.6 DPO
Wajib (mulai 1 Jun 2025) jika melepasi ambang Garis Panduan DPO 25 Feb 2025 — pemprosesan berskala besar / pemantauan sistematik berkala `[ambang kuantitatif tepat: rujuk garis panduan JPDP semasa — pada skala MVP (ratusan PIC) Azan hampir pasti DI BAWAH ambang]`. Tindakan MVP: TIDAK melantik DPO formal; letak emel khusus `privasi@{domain}` dalam Notis sebagai saluran subjek data; nilai semula bila skala membesar.

## 12.7 Pendedahan kepada penyedia AI (rentas sempadan)
Data ke API AI = pendedahan kepada pemproses, kemungkinan rentas sempadan (garis panduan CBPDT 2025 wujud). Mitigasi terbina dalam sistem: (a) **prompt diminimumkan PII** (§8.3 — tiada telefon/emel/IC/akaun individu dalam prompt), (b) dinyatakan jelas dalam Notis + persetujuan L9, (c) **pilihan Ollama tempatan** disokong kelas pertama oleh driver `openai_compatible` — untuk projek/kandungan yang Azan mahu kekal dalam negara, (d) `input_snapshot` membolehkan audit apa sebenarnya dihantar, (e) **rekod TIA (Transfer Impact Assessment) ringkas per penyedia AI** — negara destinasi, kategori data dihantar (selepas minimisasi), asas pemindahan (persetujuan L9 / keperluan kontrak) — templat 1 muka dilampirkan dalam `docs/SOP-PELANGGARAN-DATA.md` (dijana Fasa 9).

## 12.8 Penyimpanan & pemadaman (prinsip penyimpanan s.10)
| Data | Tempoh | Mekanisme |
|---|---|---|
| Lead ditolak | 6 bulan | command `reka:prune` (jadual harian) |
| Projek `cancelled/expired` | 12 bulan | prune + padam aset |
| Projek siap (`live`) | simpan (rekod kontrak/serahan) — semak semasa audit tahunan | manual |
| Log notifikasi & audit | 24 bulan | prune |

## 12.9 Perundangan lain (nota perniagaan — bukan keperluan kod)
- **e-Invois LHDN** `[DIBETULKAN v1.1 — DISAHKAN SEMULA 7 Julai 2026]`: Keputusan Kabinet **6 Dis 2025** menaikkan ambang pengecualian **secara kekal kepada RM1 juta** perolehan tahunan — perniagaan bawah RM1J **dikecualikan kekal** (boleh sertai secara sukarela). **Fasa RM500k–RM1J ("Fasa 5") DIBATALKAN — TIADA kewajipan baharu bermula 1 Julai 2026** bagi band ini. Pengecualian: perniagaan <RM1J yang mempunyai pemegang saham bukan individu / syarikat berkaitan berperolehan ≥RM1J — wajib mulai 1 Julai 2026. Fasa 4 (RM1J–5J) bermula 1 Jan 2026 dengan kelonggaran tanpa penalti sehingga 31 Dis 2027. Tindakan Azan: pada skala semasa, DIKECUALIKAN; pantau bila perolehan menghampiri RM1J. Sistem tidak mengeluarkan invois — tiada kod diperlukan.
- **Pendaftaran PDPA (Perintah Kelas Pengguna Data)** `[DISAHKAN]`: hanya 13 kelas ditetapkan wajib berdaftar dengan JPDP (komunikasi, perbankan/kewangan, insurans, kesihatan, pelancongan, pengangkutan, pendidikan, perkhidmatan profesional tertentu, jualan langsung, hartanah, utiliti, pajak gadai, pemberi pinjam wang). Perniagaan pembangunan web/SaaS kecil **tidak termasuk mana-mana kelas** — pendaftaran TIDAK diperlukan; kepatuhan 7 prinsip PDPA tetap wajib tanpa mengira pendaftaran.
- **Akta Keselamatan Siber 2024 (Akta 854)** `[DISAHKAN]` (berkuat kuasa 26 Ogos 2024): terpakai kepada entiti NCII (11 sektor) & pelesenan penyedia perkhidmatan keselamatan siber. REKA bukan NCII dan bukan penyedia perkhidmatan keselamatan siber — **tidak terpakai**.
- **Hak cipta:** font = Google Fonts (lesen bebas); imej stok (L6 `stok_sementara`) mesti sumber berlesen — build-brief menyatakan "gunakan sumber berlesen bebas royalti & rekod sumber"; JANGAN ambil imej dari laman masjid lain.
- **Perolehan .gov.my:** masjid kerajaan berkemungkinan tertakluk prosedur perolehan rasmi (sebut harga/ePerolehan) & domain .gov.my perlu permohonan agensi melalui MYNIC — jangan janji garis masa; lead tier ini diurus berbeza (nota pada LeadResource).
- **Kandungan:** laman yang dibina tertakluk Akta Komunikasi & Multimedia 1998 (kandungan menyalahi undang-undang) — tanggungjawab kandungan akhir pada masjid; nyatakan dalam Terma.

## 12.10 Garis Panduan JPDP 30 April 2026 — DPIA · ADMP · DPbD `[BAHARU v1.1 — DISAHKAN]`
JPDP mengeluarkan tiga garis panduan baharu pada **30 April 2026**: **DPIA** (Penilaian Impak Perlindungan Data), **ADMP** (Automated Decision-Making & Profiling), dan **DPbD** (Data Protection by Design). Penilaian untuk REKA: penjanaan draf kandungan laman web **bukan** keputusan automatik terhadap individu dan bukan pemprofilan individu — pencetus ADMP/DPIA kemungkinan besar **tidak terpakai** pada skop MVP. Tindakan wajar (kos rendah): (a) rekodkan penilaian ini secara bertulis — satu perenggan dalam `docs/SOP-PELANGGARAN-DATA.md`; (b) petakan amalan DPbD yang sudah tertanam dalam reka bentuk (minimisasi PII §8.3/§12.7, EXIF strip §11.4, mask logging §11.3, retensi §12.8) dalam dokumen yang sama; (c) **jika masa depan REKA menambah ciri yang membuat keputusan automatik tentang individu** (cth pemarkahan/penapisan permohonan), DPIA formal diperlukan SEBELUM pelancaran ciri itu. Rujukan tambahan: Garis Panduan Latihan & Kompetensi DPO (21 Julai 2025) jika DPO dilantik kelak.

---

# §13 — NOTIFIKASI

**Adapter WhatsApp generik** (`app/Services/WhatsappGateway.php`): `POST {settings.whatsapp_gateway_url}` JSON `{"phone":"60195998294","message":"..."}` + header `X-Gateway-Secret: {settings.whatsapp_gateway_secret}`; timeout 10s; dihantar melalui queued job `tries=3 backoff=60`; kegagalan → `notification_logs.failed` + fallback mail (TIDAK menyekat aliran utama). Sepadan dengan gaya gateway wassap.wehdah.my milik Azan; URL & secret dikonfigurasi di Settings `[SAHKAN payload tepat gateway sendiri semasa deploy — adapter satu fail, mudah laras]`.

| Event | Penerima | Saluran | Isi (templat penuh §16.C) |
|---|---|---|---|
| invitation.sent | PIC | WA + mail | Salam + link + luput + "simpan pautan ini" |
| wizard.reminder (3 hari idle, in_progress, max 2×, scheduler harian) | PIC | WA | "Borang anda menunggu — sambung di sini" |
| submitted | Admin | mail | Ringkasan + pautan Filament |
| generation.succeeded | PIC | WA + mail | "Draf laman {masjid} sedia — lihat & beri maklum balas: {link P5}" |
| generation.failed (muktamad) | Admin | mail | Ralat + pautan retry |
| quota.exhausted_note | Admin | WA + mail | Nota PIC + butang top-up |
| approved | Admin | WA + mail | "{masjid} LULUS draf — eksport pakej" |
| status.build_updated (in_build/in_review/live) | PIC | WA | Kemas kini + pautan P10 |
| token.expiring (5 hari sebelum, belum submit) | Admin | mail | Senarai — tindakan lanjut/tutup |

---

# §14 — PAKEJ SERAHAN (HANDOVER) — *raison d'être* sistem

## 14.1 Kandungan ZIP `handover-{slug}-{tarikh}.zip`
```
spec.json                  ← spesifikasi kanonik penuh (14.2)
build-brief.md             ← arahan Claude Code siap-guna (14.3)
content/sanity-seed.ndjson ← seed CMS (14.4)
assets/                    ← semua fail, dinamakan {kind}-{nn}-{slug}.{ext}
draft/approved-draft.html  ← snapshot draf yang diluluskan
README-HANDOVER.md         ← checklist Azan (domain, hosting, kunci, langkah)
```

## 14.2 `spec.json` — struktur kanonik (dibina `SpecBuilder` dari data hidup; dibekukan verbatim dalam `approvals.snapshot`)
```json
{
  "reka_spec_version": "1.0",
  "generated_at": "…", "approval": { "pic_name": "…", "pic_position": "…", "approved_at": "…" },
  "meta": { "tier": "…", "is_gov": false },
  "mosque": { "official_name": "…", "short_name": "…", "address": {…}, "state": "…",
              "jakim_zone": "WLY01", "authority": "…", "established_year": 1987,
              "capacity": 1500, "gps": {"lat":…, "lng":…}, "maps_url": "…",
              "phones": […], "email": "…", "socials": {…} },
  "design": { "package": "warisan_hijau", "tokens": {…}, "fonts": {…},
              "icon_style": {…}, "layout": "…", "islamic_elements": […], "mood": "…" },
  "pages": [ { "key": "…", "enabled": true, "custom_name": null } ],
  "content": { "sejarah": {"mode":"…","origin":"ai|human","…":…}, "ajk": {…},
               "services": […], "quran_classes": […], "kuliah": […], "faq": […],
               "infaq": {…}, "visitor": {…}, "…": … },
  "features": { "payment": {…}, "cms": "ajk_sendiri", "i18n": true, "prayer": {…},
                "live": {…}, "wa_button": {…}, "flags": {…} },
  "assets": [ { "kind": "hero", "file": "assets/hero-01-….jpg", "caption": null } ],
  "references": { "liked": […], "dislikes": "…" },
  "technical": { "domain": {…}, "hosting": "…", "maintenance": "…", "target_date": "…" },
  "notes": { "free_notes": "…", "budget_hint": "…", "tweak_history": […] },
  "ai_flags": [ { "path": "content.sejarah", "reviewed_by_pic": true } ]
}
```

## 14.3 `build-brief.md` — **templat Blade deterministik** (`resources/handover/build-brief.blade.php`) — BUKAN dijana AI
Struktur mencerminkan pakej 9-prompt sedia terbukti Azan (repo mamkl `docs/build-prompts.md`), slot diisi dari spec.json:
- **Pengepala:** identiti masjid, ringkasan keputusan, jadual tokens & fonts (salin-tampal sedia untuk tailwind config), zon JAKIM + endpoint disahkan §9.3 + arahan cache ≥6j.
- **MOD A (disyorkan, bila `masjid-template` wujud):** "Clone masjid-template → `site.config.ts` dari nilai ini → enable sections {senarai} → import sanity-seed.ndjson (corak skrip `migrate-to-sanity.ts` sedia ada) → ganti aset → QA checklist."
- **MOD B (fallback dari kosong):** 9 fasa mengikut struktur PROMPT 1–9 Azan, setiap fasa dengan nilai sebenar tersuntik (bukan placeholder): Prompt 1 = init + tokens + routes dari `pages[]`; Prompt 3 = waktu solat zon sebenar; dst.
- **Bahagian tetap:** peraturan §9 (Quran/waktu solat) DISALIN penuh; senarai kandungan `ai_flags` yang perlu semakan manusia; gateway pembayaran pilihan + status akaun; pek pematuhan jika `is_gov`; nota `cms_updater` menentukan Prompt 8 (Sanity) dibina atau tidak.

## 14.4 `sanity-seed.ndjson` — pemetaan ke 25 skema mamkl (`SanitySeedBuilder`)
Satu dokumen JSON per baris, `_type` + `_id` deterministik (`{type}-{slug}-{n}`): `siteSettings` (singleton: prayerZone, contact, bankInfo, whatsappChannel, officeHours, infaqCategories, faqCategoryLabels) · `service` × n (name, slug, icon, shortDescription, fullDescription, requirements, documents, fee, applyMethod, order) · `facility` · `committee` (group mapping pengurusan/wanita/belia) · `kuliahSchedule`/`weeklyKuliahSlot` · `quranClass` (enum level 1:1) · `faq` · `announcement` (seed items) · `historyArticle` + `historicalLeader` (jika mode tulis penuh) · rujukan imej = placeholder path dengan nota mapping ke `assets/` (upload imej Sanity dilakukan skrip semasa bina — di luar sistem ini).

---

# §15 — FASA PEMBINAAN CLAUDE CODE (0–9) + UJIAN

Setiap fasa: bina → `php artisan test` (ujian fasa itu) → hijau → commit → fasa seterusnya.

| Fasa | Skop | Definition of Done |
|---|---|---|
| **0** | `laravel new` (L13, PHP 8.4) · Filament ^4 (panel `/admin`, auth+2FA) · Livewire 3, Tailwind 4/Vite · struktur folder §16.E · `.env.example` §16.D · SecurityHeaders middleware · Pest sedia | App boot, `/admin` login+2FA berfungsi, header hadir |
| **1** | 20 migrasi §10 + model (casts: encrypted, json, enum) + relationships + factories + seeders: `JakimZoneSeeder` (59, §16.A), `DesignPackageSeeder` (5, §7.2), `VerseLibrarySeeder` (1, §9.2), `SettingsSeeder` · command `zones:verify` (panggil e-Solat per kod, laporan; TIDAK dijalankan dalam ujian CI) · command `reka:prune` (§12.8) + scheduler | `migrate:fresh --seed` bersih; ujian kiraan seed lulus |
| **2** | Landing `/` + `/minat` (+honeypot, rate limit, Turnstile env-gated) + terima kasih · mail admin · `LeadResource` + action **Layakkan & Jemput** (cipta project+invitation, hantar notifikasi) | Ujian: lead flow, spam ditolak, qualify mencipta token |
| **3** | Middleware `resolve.invitation` (hash, luput, revoke, kiraan) + halaman ralat mesra · P1 · `InvitationResource` (resend/extend/revoke/copy) · sweep `expired` (scheduler) | Ujian: token sah/luput/revoke; rate limit resolusi |
| **4** | Enjin wizard (Livewire `WizardStep`, autosave, progres, resume) · L0 (tier + apply preset §6.11 sekali sahaja) · L1 (zon dari DB ditapis negeri; parser GPS) · L2 + komponen `x-design-preview` §7.5 | Ujian: autosave; preset tidak menulis-ganti pilihan manual; validasi L1 |
| **5** | L3 (checklist + kluster + kaunter) · **enjin sub-borang L4** (akordion bersyarat, repeaters, semua panel §6) · L5 | Ujian: panel muncul ikut pilihan; enum quranClass dikunci; infaq pra-isi 4 |
| **6** | L6 upload (§11.4 re-encode+EXIF strip — Intervention Image) · L7, L8, L9 (consent) · P3 Semak + `CompletenessService` §6.12 + Hantar | Ujian: skor tepat kes contoh; gate menyekat; EXIF GPS terbuang; MIME palsu ditolak |
| **7** | `AiProviderResource` (+Uji Sambungan) · AiClient ×2 §8.1 · PromptBuilder (PII-minimized §12.7) · `DraftContentValidator` §8.4 · `DraftRenderer` + `shell.blade.php` §8.5 (watermark server-side) · `GenerateDraftJob` §8.6 (kunci TX, retry, kuota, cooldown) · P4 progres | Ujian: kunci menghalang serentak; kuota hanya selepas berjaya; refund kegagalan; **aksara Arab dalam output → reject**; had panjang; watermark hadir dalam HTML |
| **8** | P5 pemapar (iframe sandbox + CSP P6) · P7 tweak reka (render semula, kuota reka, tanpa AI) · P8 tweak kandungan · P9 kelulusan (snapshot beku `SpecBuilder`) · `SpecBuilder` + `SanitySeedBuilder` + `build-brief.blade.php` + **Eksport ZIP** (Filament action) | Ujian: tweak reka = 0 panggilan AI; approval membekukan; wizard baca-sahaja selepas lulus; ZIP mengandungi 6 artifak; ndjson sah baris demi baris |
| **9** | Notifikasi §13 (mail + WA adapter + logs + scheduler reminder/expiry) · P10 status + thread nota · `ProjectResource` penuh (tab, top-up, status) · Dashboard widgets + ledger kos · `/privasi` `/terma` dwibahasa · `docs/SOP-PELANGGARAN-DATA.md` · audit hooks · mask() logging · `deploy/` (nginx.conf contoh, supervisor `queue:work --queue=ai,default`, cron `schedule:run`, backup.sh) · README | **Semua ujian §15.11 hijau**, `npm run build` bersih |

## 15.11 Senarai ujian Pest minimum (26)
lead_form_validates_and_saves · lead_honeypot_silently_drops · lead_rate_limited · qualify_creates_project_and_invitation · token_resolves_valid · token_rejects_expired_and_revoked · token_resolution_rate_limited · autosave_persists_section · preset_applied_once_only · completeness_score_correct · submit_blocked_below_100 · upload_rejects_bad_mime_and_size · upload_strips_exif_gps · generate_blocked_before_submit · generation_lock_prevents_concurrent · quota_increment_only_on_success · failed_generation_refunds_quota · cooldown_enforced · arabic_output_rejected · length_violation_rejected · draft_contains_watermark_and_noindex · design_rerender_no_ai_no_quota · approval_freezes_snapshot_and_locks_wizard · handover_zip_contains_all_artifacts · sanity_ndjson_valid · notes_thread_and_status_transitions_guarded. (+ smoke: security headers hadir, admin_requires_2fa, zones_seed_count_59.)

**Nota ujian AI:** `AiClient` di-*fake* melalui `Http::fake()` — ujian TIDAK memanggil API sebenar; fixture JSON sah & tidak sah disediakan `tests/Fixtures/`.

---

# §16 — LAMPIRAN SEED & KONFIGURASI

## 16.A Senarai 59 zon JAKIM (seed `jakim_zones`)
`[DISAHKAN: format kod [A-Z]{3}0[1-9]; jumlah 59; JHR/KDH/KTN/MLK disahkan verbatim dari rujukan komuniti; KTN hanya 01 & 03 — TIADA KTN02. Label daerah SBH/SWK dari pengetahuan lazim — kod adalah kritikal, label hanyalah paparan UI. WAJIB: jalankan `php artisan zones:verify` selepas seed pertama di produksi — command memanggil e-Solat setiap kod & menandakan verified_at; mana-mana kod gagal = HENTIKAN & semak. Rujukan silang label: portal rasmi e-solat.gov.my atau api.waktusolat.app SAHAJA — repo acfatah/jakim-esolat-api DIKENAL PASTI LAPUK pada Julai 2026 (menyenaraikan N9 dengan 2 zon sahaja sedangkan portal rasmi ada 3: NGS01–03) dan TIDAK boleh dijadikan sumber]`

**Johor:** JHR01 Pulau Aur, Pulau Pemanggil · JHR02 Johor Bahru, Kota Tinggi, Mersing, Kulai · JHR03 Kluang, Pontian · JHR04 Batu Pahat, Muar, Segamat, Gemas Johor
**Kedah:** KDH01 Kota Setar, Kubang Pasu, Pokok Sena · KDH02 Kuala Muda, Yan, Pendang · KDH03 Padang Terap, Sik · KDH04 Baling · KDH05 Bandar Baharu, Kulim · KDH06 Langkawi · KDH07 Puncak Gunung Jerai
**Kelantan:** KTN01 Bachok, Kota Bharu, Machang, Pasir Mas, Pasir Puteh, Tanah Merah, Tumpat, Kuala Krai, Mukim Chiku · KTN03 Gua Musang (Galas & Bertam), Jeli, Jajahan Lojing
**Melaka:** MLK01 Seluruh Negeri Melaka
**N. Sembilan:** NGS01 Tampin, Jempol · NGS02 Jelebu, Kuala Pilah, Rembau · NGS03 Port Dickson, Seremban
**Pahang:** PHG01 Pulau Tioman · PHG02 Kuantan, Pekan, Muadzam Shah `[label mengikut portal rasmi Julai 2026 — senarai lama menyertakan Rompin; kod tetap sah, label boleh dimurnikan selepas zones:verify]` · PHG03 Jerantut, Temerloh, Maran, Bera, Chenor, Jengka · PHG04 Bentong, Lipis, Raub · PHG05 Genting Sempah, Janda Baik, Bukit Tinggi · PHG06 Cameron Highlands, Genting Highlands, Bukit Fraser
**Perlis:** PLS01 Seluruh Negeri Perlis · **P. Pinang:** PNG01 Seluruh Negeri Pulau Pinang
**Perak:** PRK01 Tapah, Slim River, Tanjung Malim · PRK02 Kuala Kangsar, Sg. Siput, Ipoh, Batu Gajah, Kampar · PRK03 Lenggong, Pengkalan Hulu, Grik · PRK04 Temengor, Belum · PRK05 Kg. Gajah, Teluk Intan, Bagan Datuk, Seri Iskandar, Beruas, Parit, Lumut, Sitiawan, Pulau Pangkor · PRK06 Selama, Taiping, Bagan Serai, Parit Buntar · PRK07 Bukit Larut
**Sabah:** SBH01 Bhg. Sandakan (Timur): Bandar Sandakan, Bukit Garam, Semawang, Temanggong, Tambisan, Sukau · SBH02 Bhg. Sandakan (Barat): Beluran, Telupid, Pinangah, Terusan, Kuamut · SBH03 Bhg. Tawau (Timur): Lahad Datu, Silabukan, Kunak, Sahabat, Semporna, Tungku · SBH04 Bhg. Tawau (Barat): Bandar Tawau, Balong, Merotai, Kalabakan · SBH05 Bhg. Kudat: Kudat, Kota Marudu, Pitas, Pulau Banggi · SBH06 Gunung Kinabalu · SBH07 Bhg. Pantai Barat: Kota Kinabalu, Ranau, Kota Belud, Tuaran, Penampang, Papar, Putatan · SBH08 Bhg. Pedalaman (Atas): Pensiangan, Keningau, Tambunan, Nabawan · SBH09 Bhg. Pedalaman (Bawah): Beaufort, Kuala Penyu, Sipitang, Tenom, Long Pasia, Membakut, Weston
**Sarawak:** SWK01 Limbang, Lawas, Sundar, Trusan · SWK02 Miri, Niah, Bekenu, Sibuti, Marudi · SWK03 Pandan, Belaga, Suai, Tatau, Sebauh, Bintulu · SWK04 Sibu, Mukah, Dalat, Song, Igan, Oya, Balingian, Kanowit, Kapit · SWK05 Sarikei, Matu, Julau, Rajang, Daro, Bintangor, Belawai · SWK06 Lubok Antu, Sri Aman, Roban, Debak, Kabong, Lingga, Engkelili, Betong, Spaoh, Pusa, Saratok · SWK07 Serian, Simunjan, Samarahan, Sebuyau, Meludam · SWK08 Kuching, Bau, Lundu, Sematan · SWK09 Zon Khas (Kg. Patarikan)
**Selangor:** SGR01 Gombak, Petaling, Sepang, Hulu Langat, Hulu Selangor, Shah Alam · SGR02 Kuala Selangor, Sabak Bernam · SGR03 Klang, Kuala Langat
**Terengganu:** TRG01 Kuala Terengganu, Marang, Kuala Nerus · TRG02 Besut, Setiu · TRG03 Hulu Terengganu · TRG04 Dungun, Kemaman
**WP:** WLY01 Kuala Lumpur, Putrajaya · WLY02 Labuan

## 16.B Copy strings BM teras (`lang/ms/reka.php`)
Butang: `Seterusnya · Kembali · Simpan & Keluar · Hantar Maklumat · Jana Draf · Luluskan Draf Ini · Tweak Reka Bentuk (Percuma) · Tweak Kandungan (AI) · Hantar Nota` · Status projek (label BM): `Dijemput · Sedang Diisi · Telah Dihantar · Draf Sedia · Diluluskan · Pakej Dieksport · Dalam Pembinaan · Semakan Akhir · Live · Diarkib · Dibatalkan · Luput` · Progres jana: `Menganalisa maklumat masjid… · Menyusun kandungan… · Menyemak & memurnikan… · Menjana paparan draf…` · Watermark: `DRAF SAMPEL — BUKAN LAMAN SEBENAR` · Autosave: `Disimpan ✓ {masa}` · Kuota: `Jana AI: {x}/{n} · Render reka bentuk: {y}/5` · Consent PDPA (L9, penuh): `Saya bersetuju maklumat yang diberikan diproses oleh {NAMA_PERNIAGAAN} bagi tujuan penyediaan cadangan dan pembinaan laman web masjid, termasuk pemprosesan kandungan oleh penyedia perkhidmatan AI pihak ketiga yang mungkin berada di luar Malaysia, selaras dengan Notis Privasi.` · Kuasa: `Saya mengesahkan maklumat yang diberikan adalah benar dan saya diberi kuasa oleh pihak masjid/AJK untuk mewakili masjid dalam urusan ini.` · Galeri: `Saya mengesahkan kebenaran individu dalam gambar telah diperoleh, termasuk kebenaran penjaga bagi kanak-kanak.`

## 16.C `.env.example` (tambahan kepada lalai Laravel)
```
APP_NAME=REKA  APP_LOCALE=ms  APP_URL=
DB_CONNECTION=mysql …  QUEUE_CONNECTION=database  CACHE_STORE=database
MAIL_MAILER=smtp …  ADMIN_NOTIFY_EMAIL=
TURNSTILE_SITE_KEY=  TURNSTILE_SECRET_KEY=      # kosong = dilangkau
REKA_BUSINESS_NAME="…"                           # untuk notis/consent
# WhatsApp gateway & kuota dikonfigurasi dalam DB (Settings page), bukan env
```

## 16.D Struktur folder (tambahan)
```
app/Services/{Ai/{AiClient.php,AnthropicClient.php,OpenAiCompatibleClient.php,PromptBuilder.php,DraftContentValidator.php},DraftRenderer.php,SpecBuilder.php,SanitySeedBuilder.php,CompletenessService.php,WhatsappGateway.php}
app/Jobs/GenerateDraftJob.php  app/Livewire/Wizard/…  app/Filament/…
resources/views/{landing,wizard,draft/shell.blade.php,status}  resources/prompts/  resources/handover/
database/seeders/{JakimZoneSeeder,DesignPackageSeeder,VerseLibrarySeeder,SettingsSeeder}
docs/SOP-PELANGGARAN-DATA.md  deploy/{nginx.conf,supervisor.conf,backup.sh}
```

---

# §17 — RISIKO & KEBURUKAN (penilaian jujur — baca sebelum melabur masa)

1. **masaj.id percuma, 5,000+ masjid.** Jika nilai "custom + kualiti + diurus" tidak jelas pada landing & pitch, prospek akan tanya "kenapa bayar?". Mitigasi: §1.2 wajib; tunjuk mamkl.my sebagai bukti kualiti.
2. **Beban selenggara N laman** — risiko perniagaan terbesar jangka panjang. Tanpa §2.5 (master template), setiap laman = codebase unik. Template = pelaburan 3–5 hari yang WAJIB dibuat sebelum masjid ke-3.
3. **Jurang ekspektasi draf vs laman siap.** Draf = 1 halaman utama contoh. PIC mungkin sangka itu produk. Mitigasi terbina: banner/watermark/copy — tetapi komunikasi manusia Azan tetap penting semasa qualify.
4. **Vektor kos AI:** retry storm (kunci+backoff ✓), prompt bengkak (PII-min + had medan ✓), spam jana (kuota+cooldown+siling 10/hari ✓), provider mahal (ledger + Uji Sambungan ✓). Baki risiko: PIC yang tweak 2× lalu minta top-up berulang — keputusan manusia Azan.
5. **PIC bukan pembuat keputusan.** Perakuan kuasa + link boleh kongsi mengurangkan, tidak menghapuskan. Realiti: sesetengah kelulusan akan diikuti "AJK tak setuju" — polisi semakan semula = keputusan perniagaan, bukan sistem.
6. **e-Solat downtime/perubahan API.** Sistem intake tidak bergantung padanya (hanya `zones:verify` manual). Laman sebenar: cache ≥6j + fallback (arahan build-brief). Risiko rendah tetapi nyata — API kerajaan tanpa SLA.
7. **Token dikongsi/bocor.** Diterima secara sedar (§11.1); kesan terhad kerana tiada data kewangan peribadi & tindakan muktamad berperakuan.
8. **Bus factor = Azan.** Satu admin, satu VPS. Backup harian ✓; dokumen SOP ✓; selebihnya risiko diterima MVP.
9. **Masjid .gov.my:** kitaran perolehan/kelulusan panjang; jangan masukkan dalam unjuran hasil awal.
10. **Abandonment wizard.** Walau ada preset+autosave+reminder, sebahagian PIC akan berhenti separuh jalan. Metrik corong di dashboard membolehkan Azan campur tangan (telefon) — reka bentuk sistem ini mengandaikan sentuhan manusia, bukan menggantikannya.

# §18 — BACKLOG PASCA-MVP (JANGAN bina sekarang)
Import Facebook (port kod fbImport/FbSyncButton mamkl) · bantuan AI dalam-wizard (karang dari bullet semasa mengisi) · repo `masjid-template` + mod eksport A aktif penuh · mod paparan TV · komen AJK pada draf · diff versi draf · pembayaran deposit (ToyyibPay) · multi-admin & peranan · PWA/notifikasi jemaah (gandingan gateway WhatsApp) · screenshot draf automatik (headless) · portal warga (.gov.my) · penilaian DPIA formal bila skala >20k subjek.

---
## SEJARAH VERSI
- **v1.1 — 7 Julai 2026 (petang):** Semakan fakta bebas kedua (research mendalam). **Pembetulan:** e-Invois §12.9 (pengecualian kekal RM1J, Fasa 5 dibatalkan — teks lama SALAH); rujukan silang zon §16.A (repo acfatah lapuk → portal rasmi/api.waktusolat.app) + label PHG02; medan `dhuha` §9.3 dijadikan PILIHAN; nota driver Intervention Image §11.4; tempoh 72 jam §12.5 diperjelas. **Tambahan:** §12.10 garis panduan JPDP 30 Apr 2026 (DPIA/ADMP/DPbD); rekod TIA §12.7; MAINPP dalam datalist §6 L1; contoh model & anggaran harga semasa §8.1/§8.8; nota json_object §8.1; status Akta 854 & pendaftaran PDPA §12.9. **Skop sistem TIDAK berubah** — tiada jadual, route, atau ciri ditambah/dibuang.
- **v1.0 — 7 Julai 2026 (pagi):** Terbitan pertama.

*Tamat spek v1.1 — 7 Julai 2026. Fakta luaran disahkan pada tarikh ini (dua pusingan verifikasi); semak semula tanda `[SAHKAN SEMULA]` jika pelaksanaan bermula >60 hari selepas tarikh spek.*
