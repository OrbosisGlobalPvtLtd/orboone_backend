@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'searchable' => false,
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'id' => null,
    'class' => '',
    'help' => null,
])

@php
    $elementId = $id ?? ($name . '_' . uniqid());
    $currentValue = $selected ?? old($name);
    $placeholderText = $placeholder ?? ($label ? 'Select ' . $label : 'Select an option');
    $isSearchable = filter_var($searchable, FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="orb-form-group {{ $attributes->has('wrapper-class') ? $attributes->get('wrapper-class') : 'mb-3' }}">
    @if($label)
        <label for="{{ $elementId }}" class="orb-form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <select 
        name="{{ $multiple ? $name.'[]' : $name }}" 
        id="{{ $elementId }}"
        class="form-control {{ $isSearchable ? 'select2-searchable' : 'custom-select' }} {{ $class }} @error($name) is-invalid @enderror"
        data-placeholder="{{ $placeholderText }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes->except(['class', 'wrapper-class']) }}
        style="width: 100%;"
    >
        @if(!$multiple)
            <option value="">{{ $placeholderText }}</option>
        @endif

        @foreach($options as $key => $option)
            @php
                if (is_object($option)) {
                    $optValue = $option->id ?? $option->value ?? $key;
                    $optLabel = $option->name ?? $option->title ?? $option->label ?? $optValue;
                } elseif (is_array($option)) {
                    $optValue = $option['id'] ?? $option['value'] ?? $key;
                    $optLabel = $option['name'] ?? $option['title'] ?? $option['label'] ?? $optValue;
                } else {
                    $optValue = $key;
                    $optLabel = $option;
                }

                $isSelected = false;
                if ($multiple && is_array($currentValue)) {
                    $isSelected = in_array((string) $optValue, array_map('strval', $currentValue), true);
                } else {
                    $isSelected = ((string) $currentValue === (string) $optValue && $currentValue !== null && $currentValue !== '');
                }
            @endphp
            <option value="{{ $optValue }}" {{ $isSelected ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>

    @if($help)
        <small class="form-text text-muted mt-1">{{ $help }}</small>
    @endif

    @error($name)
        <small class="text-danger d-block mt-1 font-weight-bold">{{ $message }}</small>
    @enderror
</div>
