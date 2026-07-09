@props([
    'value' => 0,
    'size' => 132,
    'stroke' => 10,
])

@php
    $pct = max(0, min(100, (int) $value));
    $r = ($size - $stroke) / 2;
    $circ = 2 * M_PI * $r;
    $offset = $circ * (1 - $pct / 100);
    $c = $size / 2;
    $complete = $pct >= 100;
@endphp

<div {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center']) }} style="width: {{ $size }}px; height: {{ $size }}px">
    <svg class="-rotate-90" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
        <circle cx="{{ $c }}" cy="{{ $c }}" r="{{ $r }}" fill="none" stroke="currentColor" class="text-sand" stroke-width="{{ $stroke }}" />
        <circle cx="{{ $c }}" cy="{{ $c }}" r="{{ $r }}" fill="none"
            class="{{ $complete ? 'text-brand-600' : 'text-gold-400' }} transition-all duration-700 ease-out"
            stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round"
            stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $offset }}" />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span class="font-display text-2xl font-bold text-brand-800">{{ $pct }}%</span>
        {{ $slot }}
    </div>
</div>
