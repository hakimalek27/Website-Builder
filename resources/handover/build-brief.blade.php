# BUILD BRIEF — {{ $spec['mosque']['official_name'] }}

**Dijana:** {{ $spec['generated_at'] }} · **Versi spec:** {{ $spec['reka_spec_version'] }}
**Tier:** {{ $spec['meta']['tier'] }} · **Kerajaan (.gov.my):** {{ $spec['meta']['is_gov'] ? 'YA' : 'Tidak' }}
@if ($spec['approval'])
**Diluluskan oleh:** {{ $spec['approval']['pic_name'] }} ({{ $spec['approval']['pic_position'] }}) pada {{ $spec['approval']['approved_at'] }}
@endif

---

## 1. Ringkasan Keputusan

- **Pakej reka bentuk:** {{ $spec['design']['package'] }}
- **Susun atur utama:** {{ $spec['design']['layout'] }}
- **Nada penulisan (mood):** {{ $spec['design']['mood'] }}
- **CMS:** {{ $spec['features']['cms'] }} — {{ $spec['features']['cms'] === 'ajk_sendiri' ? 'Pasang Sanity (Prompt 8)' : 'Sanity TIDAK diperlukan' }}
- **Dwibahasa:** {{ $spec['features']['i18n'] ? 'YA (BM + English)' : 'BM sahaja' }}

## 2. Design Tokens (salin ke tailwind config)

```js
colors: {
  primary: '{{ $spec['design']['tokens']['primary'] ?? '#1B5E3F' }}',
  primaryDark: '{{ $spec['design']['tokens']['primaryDark'] ?? '#0F3D27' }}',
  accent: '{{ $spec['design']['tokens']['accent'] ?? '#C9A961' }}',
  ink: '{{ $spec['design']['tokens']['ink'] ?? '#1A1A1A' }}',
  bg: '{{ $spec['design']['tokens']['bg'] ?? '#FAF7F2' }}',
  bgAlt: '{{ $spec['design']['tokens']['bgAlt'] ?? '#EFE8DC' }}',
}
```

**Fonts:** body `{{ $spec['design']['fonts']['body'] ?? '' }}` · display `{{ $spec['design']['fonts']['display'] ?? '' }}` · Arab `{{ $spec['design']['fonts']['arabic'] ?? 'Amiri' }}`

## 3. Waktu Solat — JAKIM e-Solat SAHAJA (WAJIB §9.3)

- **Zon:** {{ $spec['mosque']['jakim_zone'] }}
- **Endpoint (DISAHKAN):** `GET https://www.e-solat.gov.my/index.php?r=esolatApi/takwimsolat&period=today&zone={{ $spec['mosque']['jakim_zone'] }}`
- Server Component + cache/revalidate **≥ 6 jam** + fallback pautan e-solat.gov.my. JANGAN panggil setiap tick countdown.
- Parser MESTI melayan medan `dhuha` sebagai PILIHAN (mungkin tiada dalam respons).

---

## MOD A — Template (DISYORKAN, bila repo `masjid-template` wujud)

1. Clone `masjid-template`.
2. Isi `site.config.ts` dari nilai di atas (tokens, fonts, zon, mosque info).
3. Enable sections berikut:
@foreach (array_filter($spec['pages'], fn ($p) => $p['enabled']) as $p)
   - `{{ $p['key'] }}`@if ($p['custom_name']) ({{ $p['custom_name'] }})@endif
@endforeach
4. Import `content/sanity-seed.ndjson` (guna skrip `migrate-to-sanity.ts` sedia ada).
5. Ganti aset dari folder `assets/`.
6. Jalankan QA checklist (di bawah).

## MOD B — Dari kosong (fallback, struktur 9-prompt)

- **Prompt 1 (Init + tokens):** Next.js 16 + Tailwind + tokens seksyen 2; routes dari `pages[]` di atas.
- **Prompt 2 (Layout/Header/Footer):** nama masjid `{{ $spec['mosque']['official_name'] }}`.
- **Prompt 3 (Waktu solat):** zon `{{ $spec['mosque']['jakim_zone'] }}` (lihat seksyen 3).
- **Prompt 4 (Kandungan teras):** hero, tentang, khidmat.
- **Prompt 5 (Kelas/Kuliah):** {{ count($spec['content']['quran_classes']) }} kelas Quran, {{ count($spec['content']['kuliah']) }} kuliah.
- **Prompt 6 (Galeri/Berita):** {{ count($spec['content']['news_seed']) }} item berita permulaan.
- **Prompt 7 (Infaq):** bank {{ $spec['content']['infaq']['bank_name'] ?? '—' }} (nombor akaun dalam spec.json).
- **Prompt 8 (CMS Sanity):** {{ $spec['features']['cms'] === 'ajk_sendiri' ? 'BINA (AJK kemas kini sendiri)' : 'LANGKAU' }}.
- **Prompt 9 (Deploy):** domain `{{ $spec['technical']['domain']['name'] ?? $spec['technical']['domain']['status'] }}`, hosting `{{ $spec['technical']['hosting'] }}`.

---

## PERATURAN AGAMA (§9 — WAJIB, JANGAN KOMPROMI)

1. **AI DILARANG jana teks Arab / ayat Al-Quran / hadith / doa dalam tulisan Arab.**
   Semua teks Arab pada laman MESTI datang dari sumber muktamad yang direkod manusia (verse_library).
2. **Waktu solat = JAKIM e-Solat SAHAJA.** AI tidak pernah menjana/menganggar waktu solat.
3. Ayat Al-Quran hero (jika ada) MESTI disalin TEPAT dari mushaf/sumber sah — bukan dari ingatan AI.

## KANDUNGAN DIJANA AI — PERLU SEMAKAN MANUSIA

@if (empty($spec['ai_flags']))
- Tiada kandungan dijana AI yang memerlukan semakan.
@else
@foreach ($spec['ai_flags'] as $flag)
- `{{ $flag['path'] }}` — Disemak PIC: {{ $flag['reviewed_by_pic'] ? 'YA' : 'BELUM' }}
@endforeach
@endif

## PEMBAYARAN

- **Gateway:** {{ $spec['features']['payment']['gateway'] ?? 'tiada' }} · **Status akaun:** {{ $spec['features']['payment']['status'] ?? '—' }}

@if ($spec['meta']['is_gov'])
## PEK PEMATUHAN (.gov.my)

Bina halaman statik: Notis Privasi, Dasar Keselamatan, Piagam Pelanggan, Notis Hakcipta. Aktifkan dwibahasa & maklumat korporat penuh.
@endif

---

## QA CHECKLIST

- [ ] Waktu solat dari e-Solat berfungsi (cache ≥6j, fallback ada)
- [ ] Tiada teks Arab dijana AI
- [ ] Semua aset diganti (bukan placeholder)
- [ ] Nombor akaun infaq disahkan betul
- [ ] Kandungan AI-flagged di atas telah disemak
- [ ] Responsif mudah alih
- [ ] Deploy + domain + HTTPS
