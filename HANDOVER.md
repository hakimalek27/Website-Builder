# HANDOVER — REKA (Website Builder)

Kemas kini terakhir: **9 Julai 2026** · Branch: `main` · Remote: `github.com/hakimalek27/Website-Builder`

REKA — platform tempahan & penjanaan draf laman web masjid.
Stack: **Laravel 13.19 · PHP 8.4 · Filament v4.11 · Livewire 3 · Tailwind 4 · Pest** (dev: SQLite).

---

## Status semasa

- **Fasa 0–10 siap** (spek `docs/SPEK-REKA-v1.1.md`) + **rombakan UI/UX "Premium Islamik-Moden"** (commit `2fed894`).
- **88 ujian Pest hijau** (304 assertions) · `pint` bersih · `npm run build` bersih · `migrate:fresh --seed` bersih.
- Semua kerja **di-push ke `main`**.

## Sesi terakhir — Rombakan UI/UX (commit `2fed894`)

Rombakan menyeluruh semua halaman custom kepada gaya premium (hijau zamrud + emas, hero gelap, glassmorphism, corak Islamik, tipografi display serif).

**Sistem reka (`resources/css/app.css`):**
- Token `@theme` Tailwind 4: ramp `brand-*` (zamrud, 600=#1B5E3F) + `gold-*` (400=#C9A961) + `cream/sand/ink`.
- Kelas komponen: `.btn`(+varian) `.card` `.input` `.label` `.eyebrow` `.chip` `.badge`.
- Corak `.bg-pattern-islamic` (Rub el Hizb SVG data-URI), `.bg-noise`, animasi (float/shimmer/scale-in/fade-up).
- Reveal-on-scroll: `.js .reveal` + IntersectionObserver dalam `resources/js/app.js` (**selamat tanpa-JS** — kandungan nampak jika JS mati).
- **Font di-hos-sendiri** (`@fontsource-variable/plus-jakarta-sans` + `@fontsource/cormorant-garamond`) — WAJIB kerana CSP awam `'self'`.

**Komponen Blade guna-semula:** `resources/views/components/ui/` → button, card, badge, progress, progress-ring, section-heading, logo.

**Aset jenama (`public/`):** `favicon.svg`, `favicon.ico`, `apple-touch-icon.png`, `logo-reka.svg` (mihrab + bintang 8-penjuru). Ikon Lucide utiliti ditambah ke `resources/icons/lucide/` (guna `App\Support\Lucide::svg()`).

**Halaman:**
- **Awam:** landing 8 seksyen (hero gelap + mockup browser + jalur trust + pameran 5 pakej + perbandingan + proses + FAQ + CTA), minat (split pitch+borang), terima-kasih, privasi/terma, layout header-kaca + footer bercorak.
- **PIC:** layout chrome ruang kerja; home (ring progres), semak (bar-hantar melekit), jana-hub (meter kuota + stepper), draf (bingkai browser-chrome), tweak-reka/kandungan, lulus (istiadat), **status (timeline pencapaian + thread nota)** — route `pic.status` kini hantar `notes`.
- **Wizard:** shell (bar progres + nav melekit), 10 langkah (palet token, kad radio naik taraf), enjin `_field` (dropzone/repeater/cip). **Semua `wire:model`/`wire:key`/binding dikekalkan.**
- **Admin Filament:** `brandLogo` + `favicon` + `Color::hex('#1B5E3F')`.

**LUAR skop (kekal sistem token draf sendiri):** `resources/views/draft/shell.blade.php` + `resources/views/components/design-preview.blade.php`.

## Nota penting

1. **git/gh:** harness set `GH_TOKEN`+`GITHUB_TOKEN` tak sah → guna `env -u GH_TOKEN -u GITHUB_TOKEN git push / gh ...`.
2. **CSP + Vite dev:** `SecurityHeaders.php` ada gate **`local`-sahaja** (`withViteDevHosts()`) → `npm run dev` (hot-reload) berfungsi dalam browser bila `APP_ENV=local`. **Production/testing kekal ketat** (`default-src 'self'`, byte-identik). Alternatif: `npm run build` (pastikan `public/hot` tiada).
3. Uji flow PIC/wizard: jana token demo guna `LeadQualifier::qualify()` (corak `tests/Feature/Phase10/EndToEndTest.php`).

## Tindakan tertunggak sebelum go-live (bukan bug)

- `verse_library` seed = `PENDING_MANUAL_ENTRY` — **Azan WAJIB** isi teks Arab sebenar Surah At-Taubah:18 dari mushaf (R6 §9.2). Jangan taip dari ingatan.
- `php artisan zones:verify` di produksi (59 kod zon JAKIM).
- Notis privasi/terma dwibahasa & `docs/SOP-PELANGGARAN-DATA.md` = draf — perlu semakan perundangan.

## Perintah penting

```bash
php artisan test                 # 88 ujian Pest
php artisan migrate:fresh --seed # skema + seed (59 zon, 5 pakej, verse, settings)
npm run build                    # aset (guna ini untuk ujian browser tempatan)
vendor/bin/pint --dirty          # format PHP
php artisan zones:verify         # sahkan zon JAKIM (WAJIB sebelum prospek pertama)
```

Login admin dev: `admin@reka.test` / `password` (2FA app authenticator dipaksa).

Rujuk juga: `README.md`, `docs/GO-LIVE-CHECKLIST.md`, `docs/QA-RUN-F10.md`.
