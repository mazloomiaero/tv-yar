@props(['title' => null])

<section {{ $attributes->class('ui-card') }}>
    @if ($title)
        <h2 class="mb-4 text-base font-semibold text-slate-900">{{ $title }}</h2>
    @endif
    {{ $slot }}
</section>
