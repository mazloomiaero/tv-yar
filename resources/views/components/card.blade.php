@props(['title' => null, 'subtitle' => null])

<section {{ $attributes->class('ui-card') }}>
    @if ($title)
        <div class="mb-4">
            <h2 class="text-base font-bold text-slate-900">{{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    {{ $slot }}
</section>
