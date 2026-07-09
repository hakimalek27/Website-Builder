@props([
    'variant' => 'primary',
    'size' => null,
    'href' => null,
    'type' => 'button',
])

@php
    $classes = 'btn';
    $classes .= match ($variant) {
        'primary' => ' btn-primary',
        'gold' => ' btn-gold',
        'outline' => ' btn-outline',
        'ghost' => ' btn-ghost',
        'on-dark' => ' btn-on-dark',
        default => ' btn-primary',
    };
    $classes .= match ($size) {
        'lg' => ' btn-lg',
        'sm' => ' btn-sm',
        default => '',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
