@php
    $user = auth('web')->user();
@endphp

<x-layouts.panel
    :title="$title ?? 'اپراتور'"
    panel-label="پنل اپراتور"
    logout-route="logout"
    :user-label="$user?->username"
>
    <x-slot:nav>
        <x-nav-link :href="route('operator.dashboard')" :active="request()->routeIs('operator.dashboard')">داشبورد</x-nav-link>
        @can('viewAny', App\Models\Customer::class)
            <x-nav-link :href="route('operator.customers.search')" :active="request()->routeIs('operator.customers.*')">مشتریان</x-nav-link>
        @endcan
        @can('viewAny', App\Models\ServiceRequest::class)
            <x-nav-link :href="route('operator.service-requests.index')" :active="request()->routeIs('operator.service-requests.*')">درخواست‌های خدمت</x-nav-link>
            <x-nav-link :href="route('operator.calendar')" :active="request()->routeIs('operator.calendar')">تقویم ارجاع</x-nav-link>
        @endcan
        @can('viewAny', App\Models\Feedback::class)
            <x-nav-link :href="route('operator.feedback.index')" :active="request()->routeIs('operator.feedback.*')">بازخورد</x-nav-link>
        @endcan
    </x-slot:nav>

    @yield('content')
</x-layouts.panel>
