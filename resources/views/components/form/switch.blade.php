@props([
    'label',
    'name',
    'checked' => false,
    'help' => null,
    'disabled' => false,
    'value' => 1,
])

@php
    $id = $id ?? $name;
    $isChecked = (bool) old($name, $checked);
@endphp

<div class="mb-3">
    <div class="form-check form-switch">
        <input
            class="form-check-input"
            type="checkbox"
            role="switch"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($isChecked)
            @if ($disabled) disabled @endif
            {{ $attributes }}
        >
        <label class="form-check-label" for="{{ $id }}">
            {{ $label }}
            @if ($required ?? false)
                <span class="required">*</span>
            @endif
        </label>
    </div>
    @if ($help)
        <div class="form-text ms-0">{{ $help }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback d-flex">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
        </div>
    @enderror
</div>
