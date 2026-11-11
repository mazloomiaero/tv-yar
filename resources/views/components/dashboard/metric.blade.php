@props([
    'label',
    'value',
    'tone' => 'default',
])

@php
    $toneClass = match ($tone) {
        'brand' => 'dash-metric--brand',
        'soft' => 'dash-metric--soft',
        'warn' => 'dash-metric--warn',
        'ok' => 'dash-metric--ok',
        default => '',
    };
@endphp

<article {{ $attributes->class(['dash-metric', $toneClass]) }}>
    <p class="dash-metric__label">{{ $label }}</p>
    <p class="dash-metric__value">{{ $value }}</p>
    @isset($hint)
        <p class="dash-metric__hint">{{ $hint }}</p>
    @endisset
</article>
