@props([
    'submit' => true,
    'method' => null,
    'action' => null,
    'multipart' => false,
    'spinner' => 'Saving...',
    'cancelUrl' => null,
])

@php
    $httpMethod = strtoupper($method ?? 'POST');
    $bladeMethod = $httpMethod === 'POST' ? null : $httpMethod;
@endphp

<form
    @if ($action) action="{{ $action }}" @endif
    method="{{ $httpMethod === 'GET' ? 'GET' : 'POST' }}"
    @if ($multipart) enctype="multipart/form-data" @endif
    @if ($submit) data-loading @endif
    {{ $attributes }}
>
    @csrf
    @if ($bladeMethod)
        @method($bladeMethod)
    @endif

    {{ $slot }}

    <div class="d-flex align-items-center gap-2 mt-4 pt-2 border-top">
        @if ($submit)
            <button type="submit" class="btn btn-primary" data-loading-text="{{ $spinner }}">
                <i class="bi bi-check2-circle me-1"></i>{{ $submit }}
            </button>
        @endif

        @if ($cancelUrl)
            <a href="{{ $cancelUrl }}" class="btn btn-outline">
                <i class="bi bi-x-lg me-1"></i>Cancel
            </a>
        @endif

        @if (isset($actions))
            {{ $actions }}
        @endif
    </div>
</form>
