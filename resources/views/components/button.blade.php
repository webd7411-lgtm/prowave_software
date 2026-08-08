@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => null,
    'icon' => null,
    'label' => null,
    'loading' => null,
])

@php
    $classes = 'btn';
    $classes .= $variant ? " btn-{$variant}" : ' btn-primary';
    $classes .= $size ? " btn-{$size}" : '';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if ($icon)
        <i class="bi {{ $icon }}"></i>
    @endif
    @if ($label)
        <span>{{ $label }}</span>
    @endif
    {{ $slot }}
</button>
