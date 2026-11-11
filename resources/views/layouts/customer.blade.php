@php
    $customer = auth('customer')->user();
    $userLabel = trim(($customer?->first_name ?? '').' '.($customer?->last_name ?? ''));
@endphp

<x-layouts.panel
    :title="$title ?? 'مشتری'"
    panel-label="پنل مشتری"
    logout-route="customer.logout"
    :user-label="$userLabel"
>
    <x-slot:nav>
        <x-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.dashboard')">درخواست‌های من</x-nav-link>
        <x-nav-link :href="route('customer.invoices.index')" :active="request()->routeIs('customer.invoices.*')">فاکتورهای من</x-nav-link>
        <x-nav-link :href="route('customer.profile.edit')" :active="request()->routeIs('customer.profile.*')">پروفایل</x-nav-link>
    </x-slot:nav>

    @yield('content')
</x-layouts.panel>
