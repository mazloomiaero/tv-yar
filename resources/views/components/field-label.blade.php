@props([
    'for',
    'required' => false,
])

<label for="{{ $for }}" {{ $attributes->class('ui-label') }}>
    {{ $slot }}@if ($required)<span class="ui-required" aria-hidden="true">*</span>@endif
</label>
