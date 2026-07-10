# BRIEF PENUH — {{ $spec['mosque']['official_name'] }}

> Dokumen DALAMAN untuk pasukan/AI membina laman web PENGELUARAN. Mengandungi SEMUA
> butiran yang diisi PIC termasuk maklumat sensitif (bank, telefon) — jangan edar keluar.

**Dijana:** {{ $spec['generated_at'] }} · **Tier:** {{ $spec['meta']['tier'] }} · **Status:** {{ $project->status->label() }}

---

## ARAHAN UNTUK AI PEMBINA

Anda membina laman web **PENGELUARAN** untuk **{{ $spec['mosque']['official_name'] }}**. Peraturan:

1. Gunakan SEMUA data di bawah **verbatim** — JANGAN reka fakta, tarikh, nama atau angka yang tiada.
2. Kekalkan ejaan nama khas & singkatan rasmi **huruf demi huruf** (jangan "betulkan").
3. Seluruh UI dalam **Bahasa Melayu Malaysia** baku.
4. DILARANG menjana teks Arab (ayat Al-Quran/hadith/doa) sendiri — guna sumber rasmi sahaja.
@if (! empty($spec['features']['prayer']))
5. **Waktu solat** = JAKIM e-Solat SAHAJA. Zon `{{ $spec['mosque']['jakim_zone'] }}`. Endpoint: `GET https://www.e-solat.gov.my/index.php?r=esolatApi/takwimsolat&period=today&zone={{ $spec['mosque']['jakim_zone'] }}` — cache ≥ 6 jam; JANGAN panggil setiap tick countdown.
@endif
6. Item ditanda "✎ mod AI" ialah kandungan dijana AI daripada butir ringkas — SAHKAN dengan PIC.

### Design tokens (salin ke Tailwind)

```js
colors: {
  primary: '{{ $spec['design']['tokens']['primary'] ?? '#1B5E3F' }}',
  primaryDark: '{{ $spec['design']['tokens']['primaryDark'] ?? '#0F3D27' }}',
  accent: '{{ $spec['design']['tokens']['accent'] ?? '#C9A961' }}',
  ink: '{{ $spec['design']['tokens']['ink'] ?? '#1A1A1A' }}',
  bg: '{{ $spec['design']['tokens']['bg'] ?? '#FAF7F2' }}',
}
```

**Fonts:** body `{{ $spec['design']['fonts']['body'] ?? '' }}` · display `{{ $spec['design']['fonts']['display'] ?? '' }}` · **Nada:** {{ $spec['design']['mood'] ?? '—' }}

---

## Maklumat Organisasi (PENUH)

- **Nama rasmi:** {{ $spec['mosque']['official_name'] }}
- **Nama pendek:** {{ $spec['mosque']['short_name'] ?? '—' }}
- **Alamat:** {{ collect([$spec['mosque']['address']['line1'] ?? null, $spec['mosque']['address']['line2'] ?? null, $spec['mosque']['address']['postcode'] ?? null, $spec['mosque']['address']['city'] ?? null, $spec['mosque']['state'] ?? null])->filter()->implode(', ') ?: '—' }}
- **Telefon:** {{ implode(', ', $spec['mosque']['phones']) ?: '—' }}
- **E-mel:** {{ $spec['mosque']['email'] ?? '—' }}
- **Pihak berkuasa:** {{ $spec['mosque']['authority'] ?? '—' }}
- **Tahun ditubuhkan:** {{ $spec['mosque']['established_year'] ?? '—' }}
@foreach ($spec['mosque']['socials'] as $k => $v)
- **{{ ucfirst($k) }}:** {{ $v }}
@endforeach

---

## Butiran Wizard (VERBATIM)

@foreach ($blocks as $block)
{!! \App\Support\ProjectDataPresenter::markdown($block) !!}

@endforeach

---

## Nota PIC & Perbualan

@forelse ($notes as $n)
**{{ $n->author === 'pic' ? 'PIC' : 'Admin REKA' }}** ({{ $n->author_name }}) · {{ $n->created_at->format('d/m/Y H:i') }}
> {{ $n->body }}

@empty
_Tiada nota._
@endforelse

---

## Sejarah Penjanaan AI

@forelse ($generations as $g)
@php
    $snap = $g->input_snapshot ?? [];
    $isHtml = ($snap['pipeline'] ?? null) === 'html';
    $stageLine = '';
    if ($isHtml && ! empty($snap['stage1']['model'])) {
        $s1 = $snap['stage1'];
        $stageLine = '  - P1 '.$s1['model'].': '.($s1['tokens_in'] ?? 0).'/'.($s1['tokens_out'] ?? 0).' tok · USD '.number_format((float) ($s1['cost'] ?? 0), 4);
        if (! empty($snap['stage2']['model'])) {
            $s2 = $snap['stage2'];
            $stageLine .= ' · P2 '.$s2['model'].': '.($s2['tokens_in'] ?? 0).'/'.($s2['tokens_out'] ?? 0).' tok';
        }
    }
@endphp
- {{ $g->created_at->format('d/m/Y H:i') }} · {{ $g->type->value }}{{ $isHtml ? ' (HTML)' : '' }} · **{{ $g->status->value }}** · token {{ $g->tokens_in }}/{{ $g->tokens_out }} · USD {{ number_format((float) $g->cost_estimate, 4) }}
@if ($stageLine !== '')
{!! $stageLine !!}
@endif
@empty
- _Belum ada penjanaan._
@endforelse

@if (! empty($engineeredPrompt))
---

## Prompt Jurutera Terkini (saluran HTML — GPT jana prompt ini untuk GLM)

> Guna prompt ini untuk menjana semula / memperhalusi laman pengeluaran dengan AI.

~~~~
{!! $engineeredPrompt !!}
~~~~
@endif

@if ($tweaks->isNotEmpty())
---

## Thread Tweak PIC

@foreach ($tweaks as $tw)
- {{ $tw->created_at->format('d/m/Y H:i') }} · **{{ implode(', ', $tw->categories ?? []) ?: 'umum' }}** — {{ $tw->message }}
@endforeach
@endif

---

## Checklist QA (sebelum go-live)

- [ ] Waktu solat JAKIM sebenar (bukan contoh statik)
- [ ] Semua nama & ejaan disahkan PIC
- [ ] Imej sebenar (logo/hero/AJK) dimuat naik
- [ ] Maklumat bank & QR disahkan betul
- [ ] Notis privasi & terma disemak
- [ ] Watermark "DRAF" & noindex dibuang untuk pengeluaran
