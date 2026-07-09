@props([
    'eyebrow' => null,
    'title' => null,
    'align' => 'center',
    'dark' => false,
])

@php
    $wrap = $align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-2xl';
    $titleColor = $dark ? 'text-cream' : 'text-brand-800';
@endphp

<div {{ $attributes->merge(['class' => $wrap]) }}>
    @if ($eyebrow)
        <span class="{{ $dark ? 'eyebrow-dark' : 'eyebrow' }}">{{ $eyebrow }}</span>
    @endif
    @if ($title)
        <h2 class="mt-4 font-display text-4xl leading-[1.05] font-bold tracking-tight {{ $titleColor }} sm:text-5xl">{{ $title }}</h2>
    @endif
    @if (trim($slot) !== '')
        <p class="mt-4 text-base/relaxed {{ $dark ? 'text-cream/70' : 'text-ink/60' }}">{{ $slot }}</p>
    @endif
</div>
