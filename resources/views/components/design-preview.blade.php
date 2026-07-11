@props([
    'tokens' => [],
    'fonts' => ['body' => 'Plus Jakarta Sans Variable', 'display' => 'Cormorant Garamond'],
    'iconWeight' => 'sederhana',
    'iconContainer' => 'bulat-cair',
    'mosqueName' => 'Masjid Al-Hidayah',
    'layout' => 'hero-tengah',
    'mood' => null,
    'card' => 'lembut',
    'header' => 'padat',
    'footer' => 'ringkas',
    'divider' => 'tiada',
    'animations' => 'tiada',
    'islamic' => [],
    'logoUrl' => null,
    'stock' => false,
])
@php
    $t = array_merge([
        'primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961',
        'ink' => '#1A1A1A', 'bg' => '#FAF7F2', 'bgAlt' => '#EFE8DC',
    ], $tokens);
    // §Fasa 15 — janji PIC ditunjuk dalam pratonton (corak Islamik, logo, foto stok).
    $corak = (bool) data_get($islamic, 'corak_geometri', false);
    $arabesque = (bool) data_get($islamic, 'pembatas_arabesque', false);
    $stroke = \App\Support\Lucide::strokeForWeight($iconWeight);
    // Gaya bekas ikon (6 pilihan).
    $containerStyle = match ($iconContainer) {
        'bulat-penuh' => 'border-radius:9999px;background:'.$t['primary'].';color:#fff;',
        'kotak-lembut' => 'border-radius:0.75rem;background:'.$t['primary'].'1a;color:'.$t['primary'].';',
        'kotak-tegas' => 'border-radius:0.25rem;background:'.$t['primary'].';color:#fff;',
        'heksagon' => 'clip-path:polygon(25% 5%,75% 5%,100% 50%,75% 95%,25% 95%,0 50%);background:'.$t['primary'].';color:#fff;',
        'tanpa-bekas' => 'background:transparent;color:'.$t['primary'].';',
        default => 'border-radius:9999px;background:'.$t['primary'].'1a;color:'.$t['primary'].';', // bulat-cair
    };
    // Gaya kad (3 pilihan).
    $cardStyle = match ($card) {
        'garis' => 'border:1px solid '.$t['primary'].'33;border-radius:0.5rem;',
        'terapung' => 'border:0;border-radius:0.9rem;box-shadow:0 8px 20px -8px rgba(0,0,0,.25);',
        default => 'border:1px solid '.$t['bgAlt'].';border-radius:0.75rem;', // lembut
    };
    $sample = $mood ? \App\Support\Moods::sample($mood) : 'Laman rasmi komuniti anda.';
    $displayFont = "font-family: '".($fonts['display'] ?? 'serif')."', serif;";
    // Gaya header (3 varian).
    $headerBg = $header === 'gradien'
        ? 'background:linear-gradient(120deg,'.$t['primaryDark'].','.$t['primary'].');color:#fff;'
        : 'background:'.$t['primaryDark'].';color:#fff;';
    $headerCenter = $header === 'tengah';
@endphp
<div class="overflow-hidden rounded-2xl border border-[#EFE8DC] shadow-sm"
     data-header="{{ $header }}" data-footer="{{ $footer }}" data-divider="{{ $divider }}" data-animation="{{ $animations }}"
     style="background: {{ $t['bg'] }}; color: {{ $t['ink'] }}; font-family: '{{ $fonts['body'] ?? 'Plus Jakarta Sans Variable' }}', ui-sans-serif, sans-serif;">
    @if ($animations !== 'tiada')
    {{-- Animasi pratonton (varian fade/zoom) — berskop, hormati reduced-motion (§Fasa 14) --}}
    <style>
    @keyframes rkFadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
    @keyframes rkZoomIn{from{opacity:0;transform:scale(.94)}to{opacity:1;transform:none}}
    @media(prefers-reduced-motion:no-preference){
    [data-animation="fade"] .rk-sec{animation:rkFadeUp .5s both}
    [data-animation="zoom"] .rk-sec{animation:rkZoomIn .5s both}
    [data-animation] .rk-sec:nth-child(2){animation-delay:.08s}
    [data-animation] .rk-sec:nth-child(3){animation-delay:.16s}
    [data-animation] .rk-sec:nth-child(4){animation-delay:.24s}
    }
    </style>
    @endif
    {{-- Header masjid (varian: padat / gradien / tengah) --}}
    <div @class(['px-4 py-3', 'flex items-center justify-between' => ! $headerCenter, 'text-center' => $headerCenter]) style="{{ $headerBg }}">
        <span class="flex items-center gap-2 truncate font-bold" style="{{ $displayFont }}">
            @if ($logoUrl)<img src="{{ $logoUrl }}" alt="Logo" class="h-5 w-auto shrink-0 rounded bg-white/90 p-0.5" style="max-height:1.4rem" data-preview-logo>@endif
            {{ $mosqueName ?: 'Nama Masjid Anda' }}
        </span>
        <span @class(['gap-3 text-xs opacity-80', 'hidden sm:flex' => ! $headerCenter, 'mt-1 flex justify-center' => $headerCenter])><span>Utama</span><span>Aktiviti</span><span>Hubungi</span></span>
    </div>

    {{-- Hero — berubah ikut susun atur pilihan (pratonton hidup) --}}
    <div class="px-4 py-5" @if ($corak) style="background-image:radial-gradient({{ $t['accent'] }}2e 1px,transparent 1.4px);background-size:14px 14px;" data-preview-corak @endif>
        {{-- Eyebrow pil + chip janji (logo/foto stok/corak) --}}
        <div class="mb-3 flex flex-wrap items-center gap-1.5">
            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-widest"
                  style="border-color:{{ $t['primary'] }}55;background:{{ $t['primary'] }}14;color:{{ $t['primaryDark'] }};">
                <span style="width:5px;height:5px;border-radius:9999px;background:{{ $t['accent'] }};display:inline-block;"></span>Selamat Datang
            </span>
            @if ($stock)<span class="rounded-full px-2 py-0.5 text-[0.58rem] font-semibold" style="background:{{ $t['accent'] }}22;color:{{ $t['primaryDark'] }};" data-preview-stock>&#10022; Foto stok premium</span>@endif
            @if ($corak)<span class="rounded-full px-2 py-0.5 text-[0.58rem] font-semibold" style="background:{{ $t['primary'] }}14;color:{{ $t['primaryDark'] }};">&#9670; Corak Islamik</span>@endif
        </div>
        <div class="rk-sec">
        @switch($layout)
            @case('hero-belah')
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <p class="text-lg font-bold" style="color: {{ $t['primaryDark'] }}; {{ $displayFont }}">Selamat Datang</p>
                        <p class="mt-1 text-xs" style="opacity:.7;">{{ $sample }}</p>
                    </div>
                    <div class="h-16 w-20 shrink-0 rounded-lg" style="background: {{ $t['primary'] }};"></div>
                </div>
            @break

            @case('hero-penuh')
                <div class="rounded-xl px-4 py-5 text-center" style="background: {{ $t['primaryDark'] }}; color:#fff;">
                    <p class="text-lg font-bold" style="{{ $displayFont }}">Selamat Datang</p>
                    <p class="mt-1 text-xs" style="opacity:.85;">{{ $sample }}</p>
                </div>
            @break

            @case('hero-mihrab')
                <div class="mx-auto max-w-[80%] px-4 py-5 text-center" style="background: {{ $t['bgAlt'] }}; border-radius: 9999px 9999px 0.75rem 0.75rem;">
                    <p class="text-lg font-bold" style="color: {{ $t['primaryDark'] }}; {{ $displayFont }}">Selamat Datang</p>
                    <p class="mt-1 text-xs" style="opacity:.7;">{{ $sample }}</p>
                </div>
            @break

            @case('grid-kad')
                <p class="text-base font-bold" style="color: {{ $t['primaryDark'] }}; {{ $displayFont }}">Selamat Datang</p>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="h-8 rounded-lg" style="background: {{ $t['bgAlt'] }};"></div>
                    @endfor
                </div>
            @break

            @case('klasik-formal')
                <div class="border-y py-3 text-center" style="border-color: {{ $t['accent'] }};">
                    <p class="text-lg font-bold" style="color: {{ $t['primaryDark'] }}; {{ $displayFont }}">Selamat Datang</p>
                    <p class="mt-1 text-xs" style="opacity:.7;">{{ $sample }}</p>
                </div>
            @break

            @default
                {{-- hero-tengah --}}
                <div class="text-center">
                    <p class="text-lg font-bold" style="color: {{ $t['primaryDark'] }}; {{ $displayFont }}">Selamat Datang</p>
                    <p class="mt-1 text-xs" style="opacity:.7;">{{ $sample }}</p>
                </div>
        @endswitch
        </div>

        {{-- Pembatas seksyen (varian: garis-emas / lengkung) --}}
        @if ($divider === 'garis-emas')
            <div class="my-3 flex items-center justify-center gap-1.5" aria-hidden="true">
                <span style="height:2px;width:56px;background:{{ $t['accent'] }};display:block;"></span>
                <span style="color:{{ $t['accent'] }};font-size:.6rem;">&#9670;</span>
                <span style="height:2px;width:56px;background:{{ $t['accent'] }};display:block;"></span>
            </div>
        @elseif ($divider === 'lengkung')
            <div class="my-2 h-3" style="background:{{ $t['bgAlt'] }};border-radius:0 0 50% 50%/0 0 100% 100%;" aria-hidden="true"></div>
        @endif

        {{-- Pembatas arabesque (elemen Islamik) --}}
        @if ($arabesque)
            <div class="my-3 flex justify-center" aria-hidden="true" data-preview-arabesque>
                <svg width="120" height="14" viewBox="0 0 120 14" fill="none" stroke="{{ $t['accent'] }}" stroke-width="1.2">
                    <path d="M0 7 Q10 0 20 7 T40 7 T60 7 T80 7 T100 7 T120 7"/>
                </svg>
            </div>
        @endif

        {{-- Kad waktu solat contoh (data HIASAN) --}}
        <div class="rk-sec mt-3 px-3 py-2 text-xs" style="{{ $cardStyle }} background: {{ $t['bgAlt'] }};">
            <div class="flex items-center justify-between">
                <span style="opacity:.6;">Waktu solat (contoh)</span>
                <span class="font-semibold" style="color: {{ $t['primary'] }};">Maghrib 19:29</span>
            </div>
        </div>

        {{-- Kad khidmat dengan ikon gaya pilihan --}}
        <div class="rk-sec mt-3 flex items-center gap-3 px-3 py-2" style="{{ $cardStyle }}">
            <span class="flex h-9 w-9 items-center justify-center" style="{{ $containerStyle }}">
                {!! \App\Support\Lucide::svg('HeartHandshake', $stroke, 'w-5 h-5') !!}
            </span>
            <span class="text-xs">
                <span class="block font-semibold">Khidmat Kariah</span>
                <span style="opacity:.6;">Nikah, jenazah, tahlil</span>
            </span>
        </div>

        {{-- Butang primary + accent --}}
        <div class="rk-sec mt-4 flex gap-2">
            <span class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white" style="background: {{ $t['primary'] }};">Infaq Sekarang</span>
            <span class="rounded-lg px-3 py-1.5 text-xs font-semibold" style="background: {{ $t['accent'] }}; color: {{ $t['primaryDark'] }};">Hubungi</span>
        </div>
    </div>

    {{-- Footer mini (varian: ringkas / tengah-jenama / tiga-lajur) --}}
    @switch($footer)
        @case('tiga-lajur')
            <div class="grid grid-cols-3 gap-2 px-4 py-3 text-[0.6rem]" style="background: {{ $t['primaryDark'] }}; color:#fff;">
                <div><div class="mb-1 font-semibold" style="{{ $displayFont }}">{{ $mosqueName }}</div><div style="opacity:.6;">Ringkasan…</div></div>
                <div><div class="h-1.5 w-8 rounded" style="background:#ffffff3a;"></div><div class="mt-1 h-1.5 w-6 rounded" style="background:#ffffff3a;"></div></div>
                <div><div class="h-1.5 w-8 rounded" style="background:#ffffff3a;"></div><div class="mt-1 h-1.5 w-6 rounded" style="background:#ffffff3a;"></div></div>
            </div>
        @break

        @case('tengah-jenama')
            <div class="px-4 py-4 text-center" style="background: {{ $t['primaryDark'] }}; color:#fff;">
                <div class="text-sm font-bold" style="{{ $displayFont }}">{{ $mosqueName }}</div>
                <div class="mt-1 flex justify-center gap-2 text-[0.6rem]" style="opacity:.7;"><span>Utama</span><span>Aktiviti</span><span>Hubungi</span></div>
            </div>
        @break

        @default
            <div class="px-4 py-3 text-center text-[0.65rem]" style="background: {{ $t['primaryDark'] }}; color:#fff; opacity:.9;">&copy; {{ $mosqueName }}</div>
    @endswitch
</div>
