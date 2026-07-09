@props([
    'tokens' => [],
    'fonts' => ['body' => 'Plus Jakarta Sans Variable', 'display' => 'Cormorant Garamond'],
    'iconWeight' => 'sederhana',
    'iconContainer' => 'bulat-cair',
    'mosqueName' => 'Masjid Al-Hidayah',
    'layout' => 'hero-tengah',
    'mood' => null,
    'card' => 'lembut',
])
@php
    $t = array_merge([
        'primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961',
        'ink' => '#1A1A1A', 'bg' => '#FAF7F2', 'bgAlt' => '#EFE8DC',
    ], $tokens);
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
@endphp
<div class="overflow-hidden rounded-2xl border border-[#EFE8DC] shadow-sm"
     style="background: {{ $t['bg'] }}; color: {{ $t['ink'] }}; font-family: '{{ $fonts['body'] ?? 'Plus Jakarta Sans Variable' }}', ui-sans-serif, sans-serif;">
    {{-- Header masjid --}}
    <div class="flex items-center justify-between px-4 py-3" style="background: {{ $t['primaryDark'] }}; color:#fff;">
        <span class="truncate font-bold" style="{{ $displayFont }}">{{ $mosqueName ?: 'Nama Masjid Anda' }}</span>
        <span class="hidden gap-3 text-xs opacity-80 sm:flex"><span>Utama</span><span>Aktiviti</span><span>Hubungi</span></span>
    </div>

    {{-- Hero — berubah ikut susun atur pilihan (pratonton hidup) --}}
    <div class="px-4 py-5">
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

        {{-- Kad waktu solat contoh (data HIASAN) --}}
        <div class="mt-3 px-3 py-2 text-xs" style="{{ $cardStyle }} background: {{ $t['bgAlt'] }};">
            <div class="flex items-center justify-between">
                <span style="opacity:.6;">Waktu solat (contoh)</span>
                <span class="font-semibold" style="color: {{ $t['primary'] }};">Maghrib 19:29</span>
            </div>
        </div>

        {{-- Kad khidmat dengan ikon gaya pilihan --}}
        <div class="mt-3 flex items-center gap-3 px-3 py-2" style="{{ $cardStyle }}">
            <span class="flex h-9 w-9 items-center justify-center" style="{{ $containerStyle }}">
                {!! \App\Support\Lucide::svg('HeartHandshake', $stroke, 'w-5 h-5') !!}
            </span>
            <span class="text-xs">
                <span class="block font-semibold">Khidmat Kariah</span>
                <span style="opacity:.6;">Nikah, jenazah, tahlil</span>
            </span>
        </div>

        {{-- Butang primary + accent --}}
        <div class="mt-4 flex gap-2">
            <span class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white" style="background: {{ $t['primary'] }};">Infaq Sekarang</span>
            <span class="rounded-lg px-3 py-1.5 text-xs font-semibold" style="background: {{ $t['accent'] }}; color: {{ $t['primaryDark'] }};">Hubungi</span>
        </div>
    </div>
</div>
