@php
    $user = auth('web')->user();
@endphp

<x-layouts.panel
    :title="$title ?? 'حسابدار'"
    panel-label="پنل حسابدار"
    logout-route="logout"
    :user-label="$user?->username"
>
    <x-slot:nav>
        <x-nav-link :href="route('accountant.dashboard')" :active="request()->routeIs('accountant.dashboard')">داشبورد</x-nav-link>
        <x-nav-link :href="route('accountant.invoices.index')" :active="request()->routeIs('accountant.invoices.*') || request()->routeIs('accountant.service-requests.invoices.*')">فاکتورها</x-nav-link>
        <x-nav-link :href="route('accountant.payments.index')" :active="request()->routeIs('accountant.payments.*')">پرداخت‌ها</x-nav-link>
        <x-nav-link :href="route('accountant.financial-report')" :active="request()->routeIs('accountant.financial-report')">گزارش مالی</x-nav-link>
    </x-slot:nav>

    @yield('content')
</x-layouts.panel>
