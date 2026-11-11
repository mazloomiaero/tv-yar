@props([
    'label' => null,
    'name',
    'type' => 'text',
    'id' => null,
    'value' => null,
])

@php
    $id = $id ?? $name;
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="ui-label">{{ $label }}</label>
    @endif
    <input
        id="{{ $id }}"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ $attributes->class('ui-input') }}
    >
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
