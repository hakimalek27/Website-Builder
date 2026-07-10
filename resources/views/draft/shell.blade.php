{{-- §8.5 — Blade shell deterministik. AI TIDAK menulis HTML. Varian struktur = §7 pelbagaian. --}}
@php
    $t = array_merge([
        'primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961',
        'ink' => '#1A1A1A', 'bg' => '#FAF7F2', 'bgAlt' => '#EFE8DC', 'radius' => '1rem',
    ], $tokens);
    $body = $fonts['body'] ?? 'Plus Jakarta Sans';
    $display = $fonts['display'] ?? 'Cormorant Garamond';
    $arabic = $fonts['arabic'] ?? 'Amiri';
    $fontQuery = urlencode($body).':wght@400;600;700&family='.urlencode($display).':wght@500;600;700&family='.urlencode($arabic);

    // Varian struktur (dengan default supaya draf pakej asal kekal sama).
    $layout = $layout ?? 'hero-tengah';
    $header = $header ?? 'padat';
    $footer = $footer ?? 'ringkas';
    $cardStyle = $cardStyle ?? 'lembut';
    $divider = $divider ?? 'tiada';
    $animations = $animations ?? false;
    $showPrayer = $showPrayer ?? true;
    $verbatim = $verbatim ?? [];       // data wizard render LOKAL (AJK/bank/hubungi)
    $heroImage = $heroImage ?? null;

    // Gaya ikon kad khidmat.
    $iconStroke = \App\Support\Lucide::strokeForWeight($iconStyle['weight'] ?? 'sederhana');
    $iconContainer = $iconStyle['container'] ?? 'bulat-cair';
    $svcContainer = match ($iconContainer) {
        'bulat-penuh' => 'border-radius:9999px;background:var(--primary);color:#fff;',
        'kotak-lembut' => 'border-radius:.6rem;background:color-mix(in srgb,var(--primary) 12%,transparent);color:var(--primary);',
        'kotak-tegas' => 'border-radius:.25rem;background:var(--primary);color:#fff;',
        'heksagon' => 'clip-path:polygon(25% 5%,75% 5%,100% 50%,75% 95%,25% 95%,0 50%);background:var(--primary);color:#fff;',
        'tanpa-bekas' => 'background:transparent;color:var(--primary);',
        default => 'border-radius:9999px;background:color-mix(in srgb,var(--primary) 12%,transparent);color:var(--primary);',
    };
    $svcIcons = ['HeartHandshake', 'HandHeart', 'BookOpen', 'Users', 'Building', 'Sparkles'];
@endphp
<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>{{ $content['meta']['title'] ?? $project->mosque_name }} — DRAF</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family={{ $fontQuery }}&display=swap" rel="stylesheet">
<style>
:root{
  --primary:{{ $t['primary'] }};--primary-dark:{{ $t['primaryDark'] }};--accent:{{ $t['accent'] }};
  --ink:{{ $t['ink'] }};--bg:{{ $t['bg'] }};--bg-alt:{{ $t['bgAlt'] }};--radius:{{ $t['radius'] }};
  --font-body:'{{ $body }}',system-ui,sans-serif;--font-display:'{{ $display }}',Georgia,serif;--font-arabic:'{{ $arabic }}',serif;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--font-body);color:var(--ink);background:var(--bg);line-height:1.65;-webkit-font-smoothing:antialiased}
.wrap{max-width:1080px;margin:0 auto;padding:0 20px}
img{max-width:100%;display:block}
a{color:inherit;text-decoration:none}
h1,h2,h3{font-family:var(--font-display);line-height:1.2;color:var(--primary-dark)}
.section{padding:56px 0}
.eyebrow{display:inline-block;font-size:.8rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--primary);background:color-mix(in srgb,var(--primary) 12%,transparent);padding:4px 12px;border-radius:999px}
/* Banner DRAF (suntikan server) */
.draft-banner{position:sticky;top:0;z-index:50;background:var(--primary-dark);color:#fff;text-align:center;font-size:.8rem;font-weight:600;padding:8px 12px;letter-spacing:.02em}
/* Watermark diagonal berulang */
.watermark{position:fixed;inset:0;z-index:0;pointer-events:none;background-image:repeating-linear-gradient(-45deg,transparent 0 120px,rgba(0,0,0,.05) 120px 240px);}
.watermark::before{content:"DRAF DRAF DRAF DRAF DRAF DRAF DRAF DRAF DRAF DRAF DRAF DRAF DRAF DRAF DRAF DRAF";position:absolute;inset:-20%;font-size:44px;font-weight:800;color:rgba(0,0,0,.045);transform:rotate(-30deg);word-spacing:40px;line-height:180px;overflow:hidden}
.content{position:relative;z-index:1}
/* Header */
.site-header{background:var(--primary-dark);color:#fff}
.site-header .wrap{display:flex;align-items:center;justify-content:space-between;height:64px}
.brand{font-family:var(--font-display);font-weight:700;font-size:1.25rem}
.nav{display:flex;gap:18px;font-size:.85rem;opacity:.9;flex-wrap:wrap}
/* Varian header */
.hdr-gradien{background:linear-gradient(120deg,var(--primary-dark),var(--primary))}
.hdr-tengah .wrap{flex-direction:column;height:auto;padding-top:16px;padding-bottom:16px;gap:8px}
.hdr-tengah .nav{justify-content:center}
/* Hero */
.hero{background:linear-gradient(160deg,var(--bg-alt),var(--bg));text-align:center;padding:72px 0}
.hero h1{font-size:2.6rem;margin:16px 0}
.hero p{max-width:640px;margin:0 auto;color:color-mix(in srgb,var(--ink) 75%,transparent)}
.arabic{font-family:var(--font-arabic);direction:rtl;font-size:1.6rem;color:var(--primary);margin-bottom:8px}
.btn-row{display:flex;gap:12px;justify-content:center;margin-top:28px;flex-wrap:wrap}
.btn{padding:12px 26px;border-radius:12px;font-weight:600;font-size:.95rem}
.btn-primary{background:var(--primary);color:#fff}
.btn-accent{background:var(--accent);color:var(--primary-dark)}
/* Varian susun atur hero */
.layout-hero-belah{text-align:left}
.layout-hero-belah .btn-row{justify-content:flex-start}
.layout-hero-belah p{margin-left:0}
.layout-klasik-formal .wrap{border-top:2px solid var(--accent);border-bottom:2px solid var(--accent);padding:32px 20px}
.layout-hero-penuh{background:linear-gradient(150deg,var(--primary-dark),var(--primary))}
.layout-hero-penuh h1,.layout-hero-penuh p{color:#fff}
.layout-hero-penuh .eyebrow{background:rgba(255,255,255,.15);color:#fff}
.layout-hero-mihrab .wrap{max-width:760px;background:var(--bg-alt);border-radius:280px 280px var(--radius) var(--radius);padding:64px 44px}
/* Hero dengan imej muat naik (data-URI) */
.hero.hero-img{background-size:cover;background-position:center}
.hero.hero-img h1,.hero.hero-img p{color:#fff}
.hero.hero-img .eyebrow{background:rgba(255,255,255,.18);color:#fff}
/* Blok maklumat bank (verbatim) */
.bank-card{max-width:420px;margin:22px auto 0;text-align:left}
.bank-card .acc{font-size:1.05rem;font-weight:700;color:var(--primary);letter-spacing:.03em}
/* FAQ */
.faq details{background:#fff;border:1px solid var(--bg-alt);border-radius:var(--radius);padding:14px 20px;margin-bottom:10px}
.faq summary{font-weight:700;cursor:pointer;color:var(--primary-dark)}
.faq details p{margin-top:10px}
/* Jalur hubungi */
.contact-row{display:flex;gap:22px;justify-content:center;flex-wrap:wrap;font-size:.95rem}
.contact-social{margin-top:14px;display:flex;gap:16px;justify-content:center;font-size:.85rem;opacity:.8}
/* Pembatas seksyen */
.divider-garis-emas{position:relative;height:2px;background:var(--accent);width:90px;margin:0 auto}
.divider-garis-emas::after{content:"◆";position:absolute;top:-.7em;left:50%;transform:translateX(-50%);color:var(--accent);background:var(--bg);padding:0 10px;font-size:.7rem}
.divider-lengkung{height:44px;background:var(--bg-alt);border-radius:0 0 50% 50%/0 0 100% 100%}
/* Kad waktu solat statik */
.prayer{background:#fff;border:1px solid var(--bg-alt);border-radius:var(--radius);padding:20px;margin-top:32px;max-width:720px;margin-inline:auto}
.prayer-label{font-size:.75rem;color:color-mix(in srgb,var(--ink) 55%,transparent);text-align:center;margin-bottom:12px}
.prayer-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;text-align:center}
.prayer-grid div span{display:block}
.prayer-grid .name{font-size:.7rem;opacity:.6}
.prayer-grid .time{font-weight:700;color:var(--primary)}
/* Grid kad */
.grid{display:grid;gap:20px}
.grid-3{grid-template-columns:repeat(3,1fr)}
.grid-2{grid-template-columns:repeat(2,1fr)}
.card{background:#fff;border:1px solid var(--bg-alt);border-radius:var(--radius);padding:22px}
.card h3{font-size:1.15rem;margin-bottom:8px}
/* Varian kad */
.card-garis .card{border:1px solid color-mix(in srgb,var(--primary) 24%,transparent);border-radius:.5rem;box-shadow:none}
.card-terapung .card{border:0;border-radius:calc(var(--radius) + .2rem);box-shadow:0 14px 34px -14px rgba(0,0,0,.28)}
/* Ikon kad khidmat */
.svc-ic{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;margin-bottom:12px}
.svc-ic svg{width:22px;height:22px}
.stat{text-align:center}
.stat .value{font-size:2rem;font-weight:800;color:var(--primary);font-family:var(--font-display)}
.stat .label{font-size:.8rem;opacity:.65}
.ai-flag{font-size:.7rem;color:#8C6D2F;font-style:italic}
.placeholder-img{background:var(--bg-alt);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;height:200px;color:color-mix(in srgb,var(--ink) 45%,transparent);font-size:.85rem}
.section-alt{background:var(--bg-alt)}
.center{text-align:center}
footer.site{background:var(--primary-dark);color:#fff;padding:40px 0;text-align:center}
footer.site p{opacity:.85;max-width:600px;margin:0 auto;font-size:.9rem}
/* Varian footer */
footer.ftr-tengah-jenama{padding:52px 0}
footer.ftr-tiga-lajur{text-align:left}
footer.ftr-tiga-lajur .cols{display:grid;grid-template-columns:2fr 1fr 1fr;gap:28px;text-align:left}
footer.ftr-tiga-lajur .cols a{display:block;opacity:.85;font-size:.85rem;margin-top:4px}
/* Animasi halus (hormati reduced-motion) */
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
@media(prefers-reduced-motion:no-preference){.has-anim .section{animation:fadeUp .7s both}}
@media(max-width:720px){.grid-3,.grid-2{grid-template-columns:1fr}.prayer-grid{grid-template-columns:repeat(3,1fr)}.hero h1{font-size:2rem}.ftr-tiga-lajur .cols{grid-template-columns:1fr}}
</style>
</head>
<body class="card-{{ $cardStyle }}@if ($animations) has-anim @endif" data-layout="{{ $layout }}" data-header="{{ $header }}">
<div class="draft-banner">DRAF SAMPEL — BUKAN LAMAN SEBENAR · Dijana {{ $generatedAt }} · Versi {{ $version }}</div>
<div class="watermark"></div>

<div class="content">
    <header class="site-header hdr-{{ $header }}">
        <div class="wrap">
            <span class="brand">{{ $project->short_name ?: $project->mosque_name }}</span>
            <nav class="nav">
                @foreach (array_slice($pages, 0, 6) as $p)
                    <a href="#">{{ $p['label'] }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    {{-- Hero — susun atur ikut pilihan (§7) --}}
    <section class="hero layout-{{ $layout }}@if ($heroImage) hero-img @endif"
        @if ($heroImage) style="background-image:linear-gradient(rgba(15,61,39,.72),rgba(15,61,39,.72)),url('{{ $heroImage }}')" @endif>
        <div class="wrap">
            @if ($showVerse)
                <p class="arabic">{{ $verse->arabic_text }}</p>
            @endif
            @if (! empty($content['hero']['eyebrow']))
                <span class="eyebrow">{{ $content['hero']['eyebrow'] }}</span>
            @endif
            <h1>{{ $content['hero']['headline'] ?? $project->mosque_name }}</h1>
            <p>{{ $content['hero']['subheadline'] ?? '' }}</p>
            <div class="btn-row">
                @if (! empty($content['hero']['cta_primary_label']))<span class="btn btn-primary">{{ $content['hero']['cta_primary_label'] }}</span>@endif
                @if (! empty($content['hero']['cta_secondary_label']))<span class="btn btn-accent">{{ $content['hero']['cta_secondary_label'] }}</span>@endif
            </div>

            {{-- Waktu solat: blok STATIK berlabel (§8.5/§9.3) — masjid sahaja --}}
            @if ($showPrayer)
                <div class="prayer">
                    <p class="prayer-label">Contoh paparan — waktu sebenar akan diambil terus dari JAKIM e-Solat (zon {{ $zone }})</p>
                    <div class="prayer-grid">
                        @foreach (['Subuh' => '5:55', 'Syuruk' => '7:10', 'Zohor' => '13:15', 'Asar' => '16:38', 'Maghrib' => '19:29', 'Isyak' => '20:42'] as $name => $time)
                            <div><span class="name">{{ $name }}</span><span class="time">{{ $time }}</span></div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if ($divider !== 'tiada')
        <div class="wrap" style="padding-top:8px"><div class="divider-{{ $divider }}"></div></div>
    @endif

    {{-- Tentang --}}
    @if (! empty($content['about']))
        <section class="section">
            <div class="wrap grid grid-2" style="align-items:center">
                <div>
                    <span class="eyebrow">Tentang Kami</span>
                    <h2 style="margin:12px 0">{{ $content['about']['heading'] ?? 'Tentang Masjid' }}</h2>
                    @foreach (($content['about']['paragraphs'] ?? []) as $para)
                        <p style="margin-bottom:12px">{{ $para }}</p>
                    @endforeach
                    @if (in_array('sejarah', $aiFlags, true))
                        <p class="ai-flag">✎ Dijana AI — sila semak ketepatan</p>
                    @endif
                </div>
                <div class="grid grid-2">
                    @foreach (($content['about']['stats'] ?? []) as $stat)
                        <div class="card stat"><span class="value">{{ $stat['value'] ?? '' }}</span><span class="label">{{ $stat['label'] ?? '' }}</span></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Perutusan (quote AI + nama/jawatan verbatim) --}}
    @if (! empty($content['perutusan']) || ! empty($verbatim['perutusan']))
        <section class="section">
            <div class="wrap center" style="max-width:760px">
                <span class="eyebrow">Perutusan</span>
                <h2 style="margin:12px 0">{{ $content['perutusan']['heading'] ?? 'Perutusan' }}</h2>
                @if (! empty($content['perutusan']['quote']))
                    <p style="font-size:1.1rem;font-style:italic">"{{ $content['perutusan']['quote'] }}"</p>
                @endif
                @if (! empty($verbatim['perutusan']))
                    <p style="margin-top:16px;font-weight:700">{{ $verbatim['perutusan']['name'] }}</p>
                    <p style="opacity:.65;font-size:.85rem">{{ $verbatim['perutusan']['role'] ?? '' }}</p>
                @endif
                @if (in_array('perutusan', $aiFlags, true))<p class="ai-flag">✎ Dijana AI — sila semak</p>@endif
            </div>
        </section>
    @endif

    {{-- Visi & Misi --}}
    @if (! empty($content['visi_misi']))
        <section class="section section-alt">
            <div class="wrap">
                <h2 class="center" style="margin-bottom:28px">Visi &amp; Misi</h2>
                <div class="grid grid-2">
                    @if (! empty($content['visi_misi']['visi']))
                        <div class="card"><h3>Visi</h3><p>{{ $content['visi_misi']['visi'] }}</p></div>
                    @endif
                    @if (! empty($content['visi_misi']['misi']))
                        <div class="card"><h3>Misi</h3><p>{{ $content['visi_misi']['misi'] }}</p></div>
                    @endif
                </div>
                @if (! empty($content['visi_misi']['moto']))
                    <p class="center" style="margin-top:20px;font-style:italic;color:var(--primary)">"{{ $content['visi_misi']['moto'] }}"</p>
                @endif
            </div>
        </section>
    @endif

    {{-- Jawatankuasa (AJK verbatim) --}}
    @if (! empty($verbatim['ajk']['members']))
        <section class="section">
            <div class="wrap">
                <h2 class="center" style="margin-bottom:28px">Jawatankuasa</h2>
                <div class="grid grid-3">
                    @foreach ($verbatim['ajk']['members'] as $m)
                        <div class="card center"><h3 style="font-size:1rem">{{ $m['name'] }}</h3><p style="opacity:.65;font-size:.85rem">{{ $m['position'] ?? '' }}</p></div>
                    @endforeach
                </div>
                @if ($verbatim['ajk']['total'] > count($verbatim['ajk']['members']))
                    <p class="center" style="margin-top:16px;opacity:.6;font-size:.85rem">Senarai penuh ({{ $verbatim['ajk']['total'] }} ahli) akan dipaparkan di laman sebenar.</p>
                @endif
            </div>
        </section>
    @endif

    {{-- Khidmat --}}
    @if (! empty($content['services']))
        <section class="section section-alt">
            <div class="wrap">
                <h2 class="center" style="margin-bottom:28px">Khidmat Kariah</h2>
                <div class="grid grid-3">
                    @foreach ($content['services'] as $i => $svc)
                        <div class="card">
                            <span class="svc-ic" style="{{ $svcContainer }}">{!! \App\Support\Lucide::svg($svcIcons[$i % count($svcIcons)], $iconStroke, '') !!}</span>
                            <h3>{{ $svc['title'] ?? '' }}</h3><p>{{ $svc['blurb'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Program (NGO) — ditetapkan di Fasa 11 NGO --}}
    @if (! empty($content['programs']))
        <section class="section section-alt">
            <div class="wrap">
                <h2 class="center" style="margin-bottom:28px">Program &amp; Inisiatif</h2>
                <div class="grid grid-3">
                    @foreach ($content['programs'] as $i => $prog)
                        <div class="card">
                            <span class="svc-ic" style="{{ $svcContainer }}">{!! \App\Support\Lucide::svg($svcIcons[$i % count($svcIcons)], $iconStroke, '') !!}</span>
                            <h3>{{ $prog['title'] ?? '' }}</h3><p>{{ $prog['blurb'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Fasiliti --}}
    @if (! empty($content['facilities']))
        <section class="section">
            <div class="wrap">
                <h2 class="center" style="margin-bottom:28px">Fasiliti</h2>
                <div class="grid grid-3">
                    @foreach ($content['facilities'] as $f)
                        <div class="card"><h3>{{ $f['title'] ?? '' }}</h3><p>{{ $f['blurb'] ?? '' }}</p></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Kuliah --}}
    @if (! empty($content['kuliah']))
        <section class="section section-alt">
            <div class="wrap center">
                <h2>{{ $content['kuliah']['heading'] ?? 'Kuliah' }}</h2>
                <p style="max-width:640px;margin:12px auto 0">{{ $content['kuliah']['intro'] ?? '' }}</p>
            </div>
        </section>
    @endif

    {{-- Infaq / Derma --}}
    @if (! empty($content['infaq']))
        <section class="section">
            <div class="wrap center">
                <h2>{{ $content['infaq']['heading'] ?? 'Infaq' }}</h2>
                <p style="max-width:640px;margin:12px auto 0">{{ $content['infaq']['paragraph'] ?? '' }}</p>
                <div style="margin-top:20px"><span class="btn btn-primary">Infaq Sekarang</span></div>
                @if (! empty($verbatim['bank']))
                    <div class="card bank-card">
                        <p style="font-weight:700;margin-bottom:6px">Maklumat Sumbangan</p>
                        <p style="font-size:.9rem">{{ $verbatim['bank']['bank_name'] }}</p>
                        <p class="acc">{{ $verbatim['bank']['bank_account'] }}</p>
                        <p style="font-size:.85rem;opacity:.7">{{ $verbatim['bank']['account_holder'] }}</p>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Derma (NGO) --}}
    @if (! empty($content['donate']))
        <section class="section">
            <div class="wrap center">
                <h2>{{ $content['donate']['heading'] ?? 'Derma / Sumbangan' }}</h2>
                <p style="max-width:640px;margin:12px auto 0">{{ $content['donate']['paragraph'] ?? '' }}</p>
                <div style="margin-top:20px"><span class="btn btn-primary">Derma Sekarang</span></div>
                @if (! empty($verbatim['bank']))
                    <div class="card bank-card">
                        <p style="font-weight:700;margin-bottom:6px">Maklumat Sumbangan</p>
                        <p style="font-size:.9rem">{{ $verbatim['bank']['bank_name'] }}</p>
                        <p class="acc">{{ $verbatim['bank']['bank_account'] }}</p>
                        <p style="font-size:.85rem;opacity:.7">{{ $verbatim['bank']['account_holder'] }}</p>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Sukarelawan / Keahlian (NGO) --}}
    @if (! empty($content['volunteer']) || ! empty($content['membership']))
        <section class="section section-alt">
            <div class="wrap grid grid-2">
                @if (! empty($content['volunteer']))
                    <div class="card center">
                        <h3>{{ $content['volunteer']['heading'] ?? 'Jadi Sukarelawan' }}</h3>
                        <p style="margin:10px 0 16px">{{ $content['volunteer']['paragraph'] ?? '' }}</p>
                        <span class="btn btn-primary">{{ $content['volunteer']['cta_label'] ?? 'Jadi Sukarelawan' }}</span>
                    </div>
                @endif
                @if (! empty($content['membership']))
                    <div class="card center">
                        <h3>{{ $content['membership']['heading'] ?? 'Daftar Ahli' }}</h3>
                        <p style="margin:10px 0 16px">{{ $content['membership']['paragraph'] ?? '' }}</p>
                        <span class="btn btn-accent">Daftar Ahli</span>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Pengumuman --}}
    @if (! empty($content['announcements']))
        <section class="section section-alt">
            <div class="wrap">
                <h2 class="center" style="margin-bottom:28px">Pengumuman</h2>
                <div class="grid grid-3">
                    @foreach ($content['announcements'] as $a)
                        <div class="card"><span class="label">{{ $a['date_label'] ?? '' }}</span><h3 style="margin:6px 0">{{ $a['title'] ?? '' }}</h3><p>{{ $a['excerpt'] ?? '' }}</p></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Info pelawat --}}
    @if (! empty($content['visitor_info']))
        <section class="section">
            <div class="wrap center">
                <h2>{{ $content['visitor_info']['heading'] ?? 'Info Pelawat' }}</h2>
                <p style="max-width:640px;margin:12px auto 0">{{ $content['visitor_info']['paragraph'] ?? '' }}</p>
            </div>
        </section>
    @endif

    {{-- Soalan Lazim (FAQ) --}}
    @if (! empty($content['faq']))
        <section class="section faq">
            <div class="wrap" style="max-width:760px">
                <h2 class="center" style="margin-bottom:28px">Soalan Lazim</h2>
                @foreach ($content['faq'] as $f)
                    <details>
                        <summary>{{ $f['q'] ?? '' }}</summary>
                        <p>{{ $f['a'] ?? '' }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Jalur hubungi (verbatim step_1 — render LOKAL sahaja) --}}
    @if (! empty($verbatim['contact']) || ! empty($verbatim['socials']))
        <section class="section">
            <div class="wrap center">
                <h2 style="margin-bottom:16px">Hubungi Kami</h2>
                <div class="contact-row">
                    @if (! empty($verbatim['contact']['phone']))<span>☎ {{ $verbatim['contact']['phone'] }}</span>@endif
                    @if (! empty($verbatim['contact']['email']))<span>✉ {{ $verbatim['contact']['email'] }}</span>@endif
                    @if (! empty($verbatim['contact']['address']))<span>📍 {{ $verbatim['contact']['address'] }}</span>@endif
                </div>
                @if (! empty($verbatim['socials']))
                    <div class="contact-social">
                        @foreach ($verbatim['socials'] as $platform => $url)
                            <a href="{{ $url }}">{{ ucfirst($platform) }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Halaman penuh laman anda --}}
    <section class="section section-alt">
        <div class="wrap">
            <h2 class="center" style="margin-bottom:28px">Halaman penuh laman anda</h2>
            <div class="grid grid-3">
                @foreach ($pages as $p)
                    <div class="card center"><h3 style="font-size:1rem">{{ $p['label'] }}</h3></div>
                @endforeach
            </div>
        </div>
    </section>

    @switch($footer)
        @case('tiga-lajur')
            <footer class="site ftr-tiga-lajur">
                <div class="wrap cols">
                    <div>
                        <p class="brand" style="color:#fff;margin-bottom:10px">{{ $project->mosque_name }}</p>
                        <p style="margin:0">{{ $content['footer_description'] ?? '' }}</p>
                    </div>
                    <div>
                        <p style="font-weight:700;margin-bottom:6px">Laman</p>
                        @foreach (array_slice($pages, 0, 4) as $p)<a href="#">{{ $p['label'] }}</a>@endforeach
                    </div>
                    <div>
                        <p style="font-weight:700;margin-bottom:6px">Hubungi</p>
                        <a href="#">{{ $project->mosque_name }}</a>
                        <a href="#">{{ $project->state }}</a>
                    </div>
                </div>
            </footer>
        @break

        @case('tengah-jenama')
            <footer class="site ftr-tengah-jenama">
                <div class="wrap">
                    <p class="brand" style="color:#fff;font-size:1.5rem;margin-bottom:12px">{{ $project->mosque_name }}</p>
                    <div class="nav" style="justify-content:center;margin-bottom:14px">
                        @foreach (array_slice($pages, 0, 5) as $p)<a href="#">{{ $p['label'] }}</a>@endforeach
                    </div>
                    <p>{{ $content['footer_description'] ?? '' }}</p>
                </div>
            </footer>
        @break

        @default
            <footer class="site">
                <div class="wrap">
                    <p class="brand" style="color:#fff;margin-bottom:10px">{{ $project->mosque_name }}</p>
                    <p>{{ $content['footer_description'] ?? '' }}</p>
                </div>
            </footer>
    @endswitch
</div>
</body>
</html>
