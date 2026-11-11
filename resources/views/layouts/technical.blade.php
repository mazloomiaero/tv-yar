@php
    $user = auth('web')->user();
@endphp

<x-layouts.panel
    :title="$title ?? 'سرپرست فنی'"
    panel-label="پنل سرپرست فنی"
    logout-route="logout"
    :user-label="$user?->username"
>
    <x-slot:nav>
        <x-nav-link :href="route('technical.dashboard')" :active="request()->routeIs('technical.dashboard')">داشبورد</x-nav-link>
        @can('viewAny', App\Models\ServiceRequest::class)
            <x-nav-link :href="route('technical.service-requests.index')" :active="request()->routeIs('technical.service-requests.*')">درخواست‌های خدمت</x-nav-link>
            <x-nav-link :href="route('technical.calendar')" :active="request()->routeIs('technical.calendar')">تقویم ارجاع</x-nav-link>
        @endcan
    </x-slot:nav>

    @yield('content')
</x-layouts.panel>
