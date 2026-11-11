@props([
    'variant' => 'primary',
    'type' => 'submit',
    'href' => null,
])

@php
    $class = match ($variant) {
        'secondary' => 'ui-btn-secondary',
        'danger' => 'ui-btn-danger',
        'ghost' => 'ui-btn-ghost',
        default => 'ui-btn-primary',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($class) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($class) }}>
        {{ $slot }}
    </button>
@endif
