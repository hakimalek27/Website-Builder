@props([
    'value' => 0,
    'tone' => 'brand',
])

@php
    $pct = max(0, min(100, (int) $value));
    $bar = $tone === 'gold' ? 'bg-gold-400' : 'bg-gradient-to-r from-brand-600 to-brand-500';
@endphp

<div {{ $attributes->merge(['class' => 'h-2 w-full overflow-hidden rounded-full bg-sand']) }} role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
    <div class="h-full rounded-full {{ $bar }} transition-[width] duration-700 ease-out" style="width: {{ $pct }}%"></div>
</div>
