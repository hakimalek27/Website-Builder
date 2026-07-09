# GO-LIVE CHECKLIST — REKA (untuk Azan)

> Senarai ini untuk Azan laksanakan sebelum melancarkan kepada PIC sebenar pertama. (Fasa 10)

## Server & infrastruktur
- ☐ PHP 8.4, MySQL 8/MariaDB, nginx (guna `deploy/nginx.conf`)
- ☐ HTTPS + HSTS aktif (sijil Let's Encrypt)
- ☐ Supervisor queue worker aktif — `deploy/supervisor.conf` (`queue:work --queue=ai,default`)
- ☐ Cron `* * * * * php artisan schedule:run`
- ☐ `deploy/backup.sh` dijadualkan **dan diuji PULIH** (restore test, bukan sekadar backup)

## Konfigurasi aplikasi
- ☐ `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` baharu dijana
- ☐ Mail sebenar (SMTP) + `ADMIN_NOTIFY_EMAIL` diisi
- ☐ `REKA_BUSINESS_NAME` diisi (untuk notis privasi & consent)
- ☐ `php artisan migrate --force` + `php artisan storage:link`
- ☐ `php artisan config:cache route:cache view:cache` (produksi)

## Data & guardrail agama (KRITIKAL)
- ☐ **`php artisan zones:verify` — 59/59 lulus** (WAJIB sebelum prospek pertama)
- ☐ **`verse_library`: gantikan `PENDING_MANUAL_ENTRY`** dengan teks Arab sebenar Surah At-Taubah:18 dari mushaf/sumber muktamad + isi `verified_by` (§9.2)

## Admin & AI
- ☐ Akaun admin dicipta + **2FA disediakan** (dipaksa semasa login pertama)
- ☐ Settings: WhatsApp gateway URL + secret diisi — **uji 1 mesej sebenar**
- ☐ Penyedia AI sebenar didaftar + **"Uji Sambungan" berjaya** + kadar harga diisi dalam `meta` (§8.8)
- ☐ Padam provider "Dev Fake" jika ada

## Perundangan (semakan Azan/penasihat)
- ☐ Notis Privasi `/privasi` — sahkan kandungan dwibahasa (draf dijana, perlu semakan)
- ☐ Terma `/terma` — sahkan
- ☐ `docs/SOP-PELANGGARAN-DATA.md` — semak & lengkapkan TIA per penyedia AI

## Ujian pra-lancar
- ☐ **Ujian penuh corong dengan telefon sebenar Azan sebagai "PIC ujian"** SEBELUM jemput PIC sebenar pertama
- ☐ `php artisan test` — semua hijau
- ☐ Semak draf dijana kelihatan betul dalam pelayar sebenar (iframe, font, warna)

## Baki risiko (baca)
- masaj.id percuma — pastikan mesej nilai tawaran jelas pada landing
- Beban selenggara N laman — bina repo `masjid-template` sebelum masjid ke-3 (§2.5, luar skop MVP)
- Jurang ekspektasi draf vs laman siap — komunikasi manusia Azan penting semasa qualify
