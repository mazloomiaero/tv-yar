@props(['type' => 'success'])

@php
    $class = $type === 'danger' ? 'ui-alert-danger' : 'ui-alert-success';
@endphp

<div {{ $attributes->class($class) }} role="alert">
    {{ $slot }}
</div>
