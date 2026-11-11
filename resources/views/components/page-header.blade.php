@props(['title', 'subtitle' => null])

<div {{ $attributes->class('mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between') }}>
    <div>
        <h1 class="ui-page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="ui-page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</div>
