@props([
    'wordmark' => true,
    'size' => 'h-9',
])

{{-- Logomark REKA: mihrab emas + bintang 8-penjuru atas jubin zamrud. Wordmark ikut warna teks induk. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg class="{{ $size }} w-auto shrink-0" viewBox="0 0 64 64" role="img" aria-label="REKA">
        <defs>
            <linearGradient id="reka-bg" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#1B5E3F" />
                <stop offset="1" stop-color="#0B2A1B" />
            </linearGradient>
            <linearGradient id="reka-gold" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#F4E9CC" />
                <stop offset="0.5" stop-color="#E9D29B" />
                <stop offset="1" stop-color="#C9A961" />
            </linearGradient>
        </defs>
        <rect x="2" y="2" width="60" height="60" rx="16" fill="url(#reka-bg)" />
        <g fill="none" stroke="url(#reka-gold)" stroke-opacity="0.35" stroke-width="1.4">
            <rect x="16" y="16" width="32" height="32" rx="3" />
            <rect x="16" y="16" width="32" height="32" rx="3" transform="rotate(45 32 32)" />
        </g>
        <path d="M32 12.5a4 4 0 1 0 2.8 6.85 3.1 3.1 0 1 1 0-5.7A3.98 3.98 0 0 0 32 12.5z" fill="url(#reka-gold)" />
        <path d="M23 47V33c0-6 4-10.5 9-10.5s9 4.5 9 10.5v14z" fill="none" stroke="url(#reka-gold)" stroke-width="3" stroke-linejoin="round" />
        <path d="M32 47V34.5" fill="none" stroke="url(#reka-gold)" stroke-width="2" stroke-opacity="0.55" stroke-linecap="round" />
    </svg>
    @if ($wordmark)
        <span class="font-display text-[1.65rem] leading-none font-bold tracking-[0.12em]">REKA</span>
    @endif
</span>
