@php
    $user = auth('web')->user();
@endphp

<x-layouts.panel
    :title="$title ?? 'مدیریت'"
    panel-label="پنل مدیر"
    logout-route="logout"
    :user-label="$user?->username"
>
    <x-slot:nav>
        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">داشبورد</x-nav-link>
        <x-nav-link :href="route('admin.staff.index')" :active="request()->routeIs('admin.staff.*')">کارکنان</x-nav-link>
        <x-nav-link :href="route('admin.customers.search')" :active="\App\Support\OpsRoute::is('customers.*')">مشتریان</x-nav-link>
        <x-nav-link :href="route('admin.service-requests.index')" :active="\App\Support\OpsRoute::is('service-requests.*')">همه درخواست‌ها</x-nav-link>
        <x-nav-link :href="route('admin.calendar')" :active="\App\Support\OpsRoute::is('calendar')">ارجاع تکنسین</x-nav-link>
        <x-nav-link :href="route('admin.device-types.index')" :active="request()->routeIs('admin.device-types.*')">انواع دستگاه</x-nav-link>
        <x-nav-link :href="route('admin.brands.index')" :active="request()->routeIs('admin.brands.*')">برندها</x-nav-link>
        <x-nav-link :href="route('admin.service-types.index')" :active="request()->routeIs('admin.service-types.*')">انواع خدمات</x-nav-link>
        <x-nav-link :href="route('admin.service-statuses.index')" :active="request()->routeIs('admin.service-statuses.*')">وضعیت‌های خدمت</x-nav-link>
        <x-nav-link :href="route('admin.custom-fields.index')" :active="request()->routeIs('admin.custom-fields.*')">فیلدهای سفارشی</x-nav-link>
        <x-nav-link :href="route('admin.skills.index')" :active="request()->routeIs('admin.skills.*')">مهارت‌ها</x-nav-link>
        <x-nav-link :href="route('admin.branches.index')" :active="request()->routeIs('admin.branches.*')">شعب</x-nav-link>
        <x-nav-link :href="route('admin.technicians.index')" :active="request()->routeIs('admin.technicians.*')">تکنسین‌ها</x-nav-link>
        <x-nav-link :href="route('admin.parts.index')" :active="request()->routeIs('admin.parts.*')">کاتالوگ قطعات</x-nav-link>
        <x-nav-link :href="route('admin.sms-settings.edit')" :active="request()->routeIs('admin.sms-settings.*')">تنظیمات پیامک</x-nav-link>
        <x-nav-link :href="route('admin.auth-branding.edit')" :active="request()->routeIs('admin.auth-branding.*')">متن صفحه ورود</x-nav-link>
        <x-nav-link :href="route('admin.website-intake.edit')" :active="request()->routeIs('admin.website-intake.*')">API فرم وب‌سایت</x-nav-link>
    </x-slot:nav>

    @yield('content')
</x-layouts.panel>
