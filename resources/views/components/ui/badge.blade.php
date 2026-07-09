@props(['tone' => 'brand'])

@php
    $classes = match ($tone) {
        'gold' => 'bg-gold-400/15 text-gold-700 ring-1 ring-gold-400/30',
        'ink' => 'bg-ink/5 text-ink/70 ring-1 ring-ink/10',
        'success' => 'bg-brand-600 text-white',
        'muted' => 'bg-sand text-ink/60',
        'on-dark' => 'bg-white/10 text-cream ring-1 ring-white/15',
        default => 'bg-brand-50 text-brand-700 ring-1 ring-brand-600/15',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $classes]) }}>{{ $slot }}</span>
