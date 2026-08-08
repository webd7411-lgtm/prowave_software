@props([
    'type' => 'info',
    'dismissible' => true,
    'title' => null,
])

@php
    $icons = [
        'success' => 'bi-check-circle',
        'danger' => 'bi-exclamation-triangle',
        'warning' => 'bi-exclamation-circle',
        'info' => 'bi-info-circle',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'alert alert-' . $type . ($dismissible ? ' alert-dismissible' : '') . ' fade show']) }} role="alert">
    <div class="d-flex align-items-start gap-2">
        <i class="bi {{ $icons[$type] ?? 'bi-info-circle' }} mt-0.5 flex-shrink-0"></i>
        <div>
            @if ($title)
                <div class="fw-600">{{ $title }}</div>
            @endif
            {{ $slot }}
        </div>
    </div>
    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
