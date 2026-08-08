@props([
    'title' => 'Nothing here yet',
    'text' => null,
    'icon' => 'bi-inbox',
    'action' => null,
    'actionText' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-icon">
        <i class="bi {{ $icon }}"></i>
    </div>
    <div class="empty-title">{{ $title }}</div>
    @if ($text)
        <div class="empty-text">{{ $text }}</div>
    @endif
    @if ($action)
        <a href="{{ $action }}" class="btn btn-soft-primary">
            <i class="bi bi-plus-lg me-1"></i>{{ $actionText ?? 'Add New' }}
        </a>
    @endif
</div>
