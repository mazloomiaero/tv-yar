@props([
    'href',
    'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->class(['ui-nav-link', 'ui-nav-link-active' => $active]) }}
    @if ($active) aria-current="page" @endif
>
    {{ $slot }}
</a>
