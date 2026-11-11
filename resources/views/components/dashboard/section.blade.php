@props([
    'title',
    'subtitle' => null,
])

<section {{ $attributes->class('dash-section') }}>
    <div class="dash-section__head">
        <div>
            <h2 class="dash-section__title">{{ $title }}</h2>
            @if ($subtitle)
                <p class="dash-section__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="dash-section__actions">{{ $actions }}</div>
        @endisset
    </div>
    <div class="dash-section__body">
        {{ $slot }}
    </div>
</section>
