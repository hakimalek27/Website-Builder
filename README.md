# REKA — Platform Tempahan & Penjanaan Draf Laman Web Masjid

Sistem intake bertoken di mana admin menjemput PIC masjid → PIC mengisi wizard 10 langkah → sistem menjana **draf laman** melalui AI → PIC semak/lulus → sistem menghasilkan **pakej serahan** untuk membina laman sebenar.

**Stack:** Laravel 13 · PHP 8.4 · Filament 4 · Livewire 3 · Tailwind 4 · Pest · MySQL 8 (dev: SQLite).

## Persediaan (dev)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install && npm run build
```

Login admin dev: `admin@reka.test` / `password` (2FA dipaksa semasa login pertama).

## Perintah penting

| Perintah | Fungsi |
|---|---|
| `php artisan migrate:fresh --seed` | Pasang skema + seed (59 zon, 5 pakej, verse, settings) |
| `php artisan test` | Jalankan semua ujian Pest |
| `php artisan zones:verify` | Sahkan 59 kod zon JAKIM dengan e-Solat (**WAJIB sebelum prospek pertama**) |
| `php artisan reka:prune` | Pemadaman retensi PDPA (§12.8) |
| `php artisan reka:sweep-expired` | Tandakan projek token luput → expired |
| `php artisan reka:reminders` | Reminder wizard + amaran token luput |
| `vendor/bin/pint` | Format kod |

> ⚠️ **JANGAN** taip teks Arab ayat Al-Quran dari ingatan. `verse_library` seed mengandungi `PENDING_MANUAL_ENTRY` — Azan WAJIB gantikan dengan teks sebenar Surah At-Taubah:18 dari mushaf sebelum go-live (§9.2).

## Seni bina

- **Awam:** landing + borang minat (lead).
- **PIC (bertoken `/b/{token}`):** wizard 10 langkah, jana draf, tweak, lulus.
- **Admin (`/admin`, Filament + 2FA):** leads, projek, jemputan, penyedia AI, eksport serahan.
- **Draf 2-lapis (§2.4):** AI keluarkan JSON kandungan sahaja → Blade shell render HTML (watermark server, tiada Tailwind pada draf).
- **Guardrail agama (§9):** AI dilarang jana teks Arab (regex reject); waktu solat = JAKIM e-Solat sahaja.

## Deploy (produksi)

Lihat `deploy/`:
- `nginx.conf` — reverse proxy + HTTPS + HSTS.
- `supervisor.conf` — queue worker (`--queue=ai,default`).
- `backup.sh` — backup harian DB + storage (14 hari; uji PULIH).
- Cron: `* * * * * php artisan schedule:run`.

Lihat juga `docs/GO-LIVE-CHECKLIST.md` (dijana Fasa 10) & `docs/SOP-PELANGGARAN-DATA.md`.

## Ujian

60+ ujian Pest merangkumi corong lead, token, wizard, AI (guardrail Arab, kuota, cooldown, refund), draf (watermark/noindex), kelulusan (snapshot beku), pakej serahan (ZIP), notifikasi. AI sentiasa `Http::fake()` — tiada API key sebenar diperlukan untuk ujian.

---
_Dibina mengikut `docs/SPEK-REKA-v1.1.md` (sumber tunggal kebenaran) melalui protokol 11-fasa `docs/PROMPT-CLAUDE-CODE-REKA-v1.1.md`._
