@props([
    'title',
    'subtitle' => null,
    'size' => null,
    'footer' => null,
])

@php
    $dialogClass = match ($size) {
        'sm' => 'modal-sm',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        default => '',
    };
@endphp

<div class="modal fade" tabindex="-1" aria-hidden="true" {{ $attributes }}>
    <div class="modal-dialog {{ $dialogClass }} modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">{{ $title }}</h5>
                    @if ($subtitle)
                        <div class="text-muted fs-xs mt-1">{{ $subtitle }}</div>
                    @endif
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{ $slot }}
            </div>

            @if ($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
