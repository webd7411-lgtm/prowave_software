@props([
    'label',
    'name',
    'icon' => null,
    'value' => '',
    'type' => 'text',
    'required' => false,
    'placeholder' => null,
    'help' => null,
    'autofocus' => false,
    'min' => null,
    'max' => null,
    'step' => null,
    'maxlength' => null,
    'rows' => null,
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name);
    $classList = 'form-control ' . ($hasError ? 'is-invalid' : '');
    $labelFor = $id . '-input';
@endphp

<div class="mb-3">
    <label for="{{ $labelFor }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="required">*</span>
        @endif
    </label>

    <div class="position-relative">
        @if ($icon)
            <i class="bi {{ $icon }} position-absolute" style="left: 0.875rem; top: 50%; transform: translateY(-50%); color: var(--pw-text-muted); pointer-events: none; z-index: 2;"></i>
        @endif

        @if ($type === 'textarea')
            <textarea
                id="{{ $labelFor }}"
                name="{{ $name }}"
                rows="{{ $rows ?? 3 }}"
                placeholder="{{ $placeholder }}"
                @class(['form-control', 'ps-4' => $icon, 'is-invalid' => $hasError])
                @required($required)
                @if ($autofocus) autofocus @endif
                {{ $attributes }}
            >{{ old($name, $value) }}</textarea>
        @elseif ($type === 'password')
            <input
                type="password"
                id="{{ $labelFor }}"
                name="{{ $name }}"
                placeholder="{{ $placeholder }}"
                value="{{ $type === 'password' ? '' : old($name, $value) }}"
                @class(['form-control', 'pe-5' => $showPasswordToggle ?? true, 'is-invalid' => $hasError])
                @required($required)
                @if ($autofocus) autofocus @endif
                {{ $attributes }}
            >
            <button type="button" class="password-toggle" data-toggle-password="{{ $labelFor }}" tabindex="-1" aria-label="Show password">
                <i class="bi bi-eye"></i>
            </button>
        @else
            <input
                type="{{ $type }}"
                id="{{ $labelFor }}"
                name="{{ $name }}"
                placeholder="{{ $placeholder }}"
                value="{{ old($name, $value) }}"
                @class(['form-control', 'ps-4' => $icon, 'is-invalid' => $hasError])
                @required($required)
                @if ($autofocus) autofocus @endif
                @if ($min !== null) min="{{ $min }}" @endif
                @if ($max !== null) max="{{ $max }}" @endif
                @if ($step !== null) step="{{ $step }}" @endif
                @if ($maxlength !== null) maxlength="{{ $maxlength }}" @endif
                {{ $attributes }}
            >
        @endif
    </div>

    @error($name)
        <div class="invalid-feedback d-flex">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
        </div>
    @enderror

    @if ($help && ! $hasError)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
