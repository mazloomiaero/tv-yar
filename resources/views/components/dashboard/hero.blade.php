@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
])

<header {{ $attributes->class('dash-hero') }}>
    <div class="dash-hero__copy">
        @if ($eyebrow)
            <p class="dash-hero__eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 class="dash-hero__title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="dash-hero__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="dash-hero__actions">{{ $actions }}</div>
    @endisset
</header>
