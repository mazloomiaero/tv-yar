@props([
    'kicker' => 'فرم',
    'title',
    'subtitle' => null,
])

<section {{ $attributes->class('ui-form-shell') }}>
    <div class="ui-form-shell__head">
        <p class="ui-form-shell__kicker">{{ $kicker }}</p>
        <h2 class="ui-form-shell__title">{{ $title }}</h2>
        @if ($subtitle)
            <p class="ui-form-shell__subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="ui-form-shell__body">
        {{ $slot }}
    </div>

    @isset($actions)
        <div class="ui-form-shell__actions">
            {{ $actions }}
        </div>
    @endisset
</section>
