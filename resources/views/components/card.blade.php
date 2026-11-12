@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'actions' => null,
    'bodyClass' => null,
    'hover' => false,
])

<div {{ $attributes->merge(['class' => 'card ' . ($hover ? 'card-hover' : '')]) }}>
    @if ($title || $actions)
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 gap-md-3">
            <div class="min-w-0">
                @if ($title)
                    <h5 class="card-title d-flex align-items-center gap-2">
                        @if ($icon)
                            <i class="bi {{ $icon }} text-primary"></i>
                        @endif
                        {{ $title }}
                    </h5>
                @endif
                @if ($subtitle)
                    <div class="card-subtitle">{{ $subtitle }}</div>
                @endif
            </div>
            @if ($actions)
                <div class="d-flex align-items-center gap-2 flex-shrink-0">{!! $actions !!}</div>
            @endif
        </div>
    @endif

    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
