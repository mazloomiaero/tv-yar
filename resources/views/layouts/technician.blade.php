@php
    $user = auth('web')->user();
@endphp

<x-layouts.panel
    :title="$title ?? 'تکنسین'"
    panel-label="پنل تکنسین"
    logout-route="logout"
    :user-label="$user?->username"
>
    <x-slot:nav>
        <x-nav-link :href="route('technician.dashboard')" :active="request()->routeIs('technician.dashboard')">داشبورد</x-nav-link>
        <x-nav-link :href="route('technician.assignments.index')" :active="request()->routeIs('technician.assignments.*') || request()->routeIs('technician.reports.*') || request()->routeIs('technician.payments.*')">ارجاع‌های من</x-nav-link>
    </x-slot:nav>

    @yield('content')
</x-layouts.panel>
