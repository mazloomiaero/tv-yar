@props([
    'label',
    'value',
    'tone' => 'default',
    'icon' => null,
    'progress' => null,
    'bar' => 'blue',
    'href' => null,
])

@php
    $toneClass = match ($tone) {
        'brand' => 'dash-metric--brand',
        'soft' => 'dash-metric--soft',
        'warn' => 'dash-metric--warn',
        'ok' => 'dash-metric--ok',
        default => '',
    };

    $iconClass = match ($icon) {
        'blue' => 'dash-metric__icon--blue',
        'orange' => 'dash-metric__icon--orange',
        'green' => 'dash-metric__icon--green',
        'purple' => 'dash-metric__icon--purple',
        default => null,
    };

    $barClass = match ($bar) {
        'orange' => 'dash-metric__bar--orange',
        'green' => 'dash-metric__bar--green',
        'purple' => 'dash-metric__bar--purple',
        default => '',
    };

    $progressValue = is_numeric($progress) ? max(0, min(100, (float) $progress)) : null;
    $tag = filled($href) ? 'a' : 'article';
@endphp

<{{ $tag }} {{ $attributes->class(['dash-metric', $toneClass, filled($href) ? 'dash-metric--link' : null])->merge(filled($href) ? ['href' => $href] : []) }}>
    <div class="dash-metric__top">
        <div class="min-w-0">
            <p class="dash-metric__label">{{ $label }}</p>
            <p class="dash-metric__value">{{ $value }}</p>
        </div>
        @if ($iconClass)
            <div class="dash-metric__icon {{ $iconClass }}" aria-hidden="true">
                @switch($icon)
                    @case('orange')
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z"/></svg>
                        @break
                    @case('green')
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                        @break
                    @case('purple')
                        <svg class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.3 6.2 20.5l1.6-6.6L3 9.5l6.7-.6L12 2.8l2.3 6.1 6.7.6-4.8 4.4 1.6 6.6Z"/></svg>
                        @break
                    @default
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
                @endswitch
            </div>
        @endif
    </div>

    @if ($progressValue !== null)
        <div class="dash-metric__bar {{ $barClass }}"><span style="width: {{ $progressValue }}%"></span></div>
    @endif

    @isset($hint)
        <p class="dash-metric__hint">{{ $hint }}</p>
    @endisset
</{{ $tag }}>
