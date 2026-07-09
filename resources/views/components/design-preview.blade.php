@props([
    'tokens' => [],
    'fonts' => ['body' => 'Plus Jakarta Sans Variable', 'display' => 'Cormorant Garamond'],
    'iconWeight' => 'sederhana',
    'iconContainer' => 'bulat-cair',
    'mosqueName' => 'Masjid Al-Hidayah',
    'layout' => 'hero-tengah',
])
@php
    $t = array_merge([
        'primary' => '#1B5E3F', 'primaryDark' => '#0F3D27', 'accent' => '#C9A961',
        'ink' => '#1A1A1A', 'bg' => '#FAF7F2', 'bgAlt' => '#EFE8DC',
    ], $tokens);
    $stroke = \App\Support\Lucide::strokeForWeight($iconWeight);
    // Gaya bekas ikon.
    $containerStyle = match ($iconContainer) {
        'bulat-penuh' => 'border-radius:9999px;background:'.$t['primary'].';color:#fff;',
        'kotak-lembut' => 'border-radius:0.75rem;background:'.$t['primary'].'1a;color:'.$t['primary'].';',
        'tanpa-bekas' => 'background:transparent;color:'.$t['primary'].';',
        default => 'border-radius:9999px;background:'.$t['primary'].'1a;color:'.$t['primary'].';', // bulat-cair
    };
@endphp
<div class="overflow-hidden rounded-2xl border border-[#EFE8DC] shadow-sm"
     style="background: {{ $t['bg'] }}; color: {{ $t['ink'] }}; font-family: '{{ $fonts['body'] ?? 'Plus Jakarta Sans Variable' }}', ui-sans-serif, sans-serif;">
    {{-- Header masjid --}}
    <div class="flex items-center justify-between px-4 py-3" style="background: {{ $t['primaryDark'] }}; color:#fff;">
        <span class="font-bold truncate" style="font-family: '{{ $fonts['display'] ?? 'serif' }}', serif;">
            {{ $mosqueName ?: 'Nama Masjid Anda' }}
        </span>
        <span class="hidden sm:flex gap-3 text-xs opacity-80">
            <span>Utama</span><span>Aktiviti</span><span>Hubungi</span>
        </span>
    </div>

    {{-- Hero + kad waktu solat --}}
    <div class="px-4 py-5">
        <p class="text-lg font-bold" style="color: {{ $t['primaryDark'] }}; font-family: '{{ $fonts['display'] ?? 'serif' }}', serif;">
            Selamat Datang
        </p>
        <p class="mt-1 text-xs" style="opacity:.7;">Laman rasmi komuniti masjid anda.</p>

        {{-- Kad waktu solat contoh (data HIASAN, bukan dari API) --}}
        <div class="mt-3 rounded-xl px-3 py-2 text-xs" style="background: {{ $t['bgAlt'] }};">
            <div class="flex items-center justify-between">
                <span style="opacity:.6;">Waktu solat (contoh)</span>
                <span class="font-semibold" style="color: {{ $t['primary'] }};">Maghrib 19:29</span>
            </div>
        </div>

        {{-- Kad khidmat dengan ikon gaya pilihan --}}
        <div class="mt-3 flex items-center gap-3 rounded-xl border px-3 py-2" style="border-color: {{ $t['bgAlt'] }};">
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
