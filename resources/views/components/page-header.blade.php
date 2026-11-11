@props(['title', 'subtitle' => null, 'eyebrow' => null])

<div {{ $attributes->class('mb-6 flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white/90 p-5 shadow-[0_10px_30px_-22px_rgba(15,23,42,0.35)] sm:flex-row sm:items-end sm:justify-between sm:p-6') }}>
    <div>
        @if ($eyebrow)
            <p class="mb-1 text-xs font-semibold tracking-[0.12em] text-teal-800 uppercase">{{ $eyebrow }}</p>
        @endif
        <h1 class="ui-page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="ui-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</div>
