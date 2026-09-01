@props([
    'label',
    'name',
    'options' => [],
    'selected' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
    'multiple' => false,
    'select2' => false,
    'icon' => null,
])

@php
    $id = $id ?? $name;
    $hasError = $errors->has($name) || $errors->has($name . '.*');
    $selected = old($name, $selected);

    // Normalize selected values to an array of strings for robust comparison
    if ($selected instanceof \Illuminate\Support\Collection) {
        $selected = $selected->all();
    }
    $selectedArray = is_array($selected)
        ? array_map('strval', $selected)
        : ($selected !== null ? [(string) $selected] : []);
@endphp

<div class="mb-3">
    <label for="{{ $id }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="required">*</span>
        @endif
    </label>

    <select
        id="{{ $id }}"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        @class([
            'form-select',
            'select2' => $select2,
            'is-invalid' => $hasError,
        ])
        @if ($multiple) multiple @endif
        @required($required)
        @if ($placeholder) data-placeholder="{{ $placeholder }}" @endif
        {{ $attributes }}
    >
        @if ($placeholder && ! $multiple)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            @if (is_array($optionLabel))
                <optgroup label="{{ $optionValue }}">
                    @foreach ($optionLabel as $subValue => $subLabel)
                        <option value="{{ $subValue }}" @selected(in_array((string) $subValue, $selectedArray))>{{ $subLabel }}</option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selectedArray))>{{ $optionLabel }}</option>
            @endif
        @endforeach
    </select>

    @error($name)
        <div class="invalid-feedback d-flex">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
        </div>
    @enderror

    @if ($help && ! $hasError)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
