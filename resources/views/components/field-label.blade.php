@props([
    'for',
    'required' => false,
])

<label for="{{ $for }}" {{ $attributes->class('ui-label') }}>
    {{ $slot }}@if ($required)<span class="ms-0.5" style="color:#dc2626">*</span>@endif
</label>
