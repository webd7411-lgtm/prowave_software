@props([
    'label',
    'value' => null,
    'icon' => null,
    'variant' => 'primary',
    'trend' => null,
    'trendUp' => true,
    'suffix' => null,
    'id' => null,
    'counter' => null,
])

@php
    $iconBg = match ($variant) {
        'success' => 'bg-emerald-soft',
        'warning' => 'bg-warning-soft',
        'danger' => 'bg-danger-soft',
        'info' => 'bg-cyan-soft',
        'secondary' => 'bg-indigo-soft',
        default => 'bg-primary-soft',
    };
@endphp

<div {{ $attributes->merge(['class' => 'card stat-card card-hover']) }}>
    <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon {{ $iconBg }}">
            <i class="bi {{ $icon ?? 'bi-circle' }}"></i>
        </div>
        <div class="flex-grow-1 min-w-0">
            <div class="stat-label text-truncate">{{ $label }}</div>
            <div class="stat-value" @if ($id) id="{{ $id }}" @endif @if ($counter) data-counter="{{ $counter }}" @endif>
                {{ $counter ? '0' : ($value ?? '0') }}
                @if ($suffix)
                    <span class="fs-sm fw-500 text-muted">{{ $suffix }}</span>
                @endif
            </div>
            @if ($trend)
                <div class="stat-trend {{ $trendUp ? 'up' : 'down' }}">
                    <i class="bi {{ $trendUp ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                    {{ $trend }}
                </div>
            @endif
        </div>
    </div>
</div>
