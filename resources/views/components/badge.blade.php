@props([
    'text' => null,
    'variant' => 'secondary',
    'soft' => true,
    'icon' => null,
])

@php
    $classes = 'badge ' . ($soft ? "badge-soft-{$variant}" : "text-bg-{$variant}");
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <i class="bi {{ $icon }} me-1"></i>
    @endif
    {{ $text ?? $slot }}
</span>
