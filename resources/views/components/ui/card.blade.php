@props(['variant' => 'default'])

@php
    $classes = match ($variant) {
        'glass' => 'card-glass',
        'dark' => 'card-dark',
        default => 'card',
    };
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</div>
