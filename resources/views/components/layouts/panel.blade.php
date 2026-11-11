@props([
    'title' => '',
    'panelLabel' => '',
    'logoutRoute' => 'logout',
    'userLabel' => '',
])

@php
    $brandCompany = null;
    if (\Illuminate\Support\Facades\Schema::hasTable('company')) {
        $brandCompany = \App\Models\Company::query()->first();
    }
    $productName = 'سامانه مدیریت خدمات فنی';
    $brandName = filled($brandCompany?->name) ? $brandCompany->name : $productName;
    $primary = filled($brandCompany?->primary_color) ? $brandCompany->primary_color : '#0f766e';
    $secondary = filled($brandCompany?->secondary_color) ? $brandCompany->secondary_color : '#0f172a';
    $logo = $brandCompany?->logo;
@endphp

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?: $panelLabel }} — {{ $brandName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-brand: {{ $primary }};
            --color-brand-secondary: {{ $secondary }};
        }
    </style>
</head>
<body class="panel-shell min-h-screen text-slate-800 antialiased">
    <a href="#panel-main" class="auth-skip">رفتن به محتوای اصلی</a>
    <div
        data-sidebar-backdrop
        class="fixed inset-0 z-30 hidden bg-slate-900/40 lg:hidden"
    ></div>

    <div class="min-h-screen lg:flex">
        <aside
            data-sidebar
            class="fixed inset-y-0 start-0 z-40 flex w-72 translate-x-full flex-col text-slate-100 transition-transform duration-200 lg:static lg:translate-x-0"
            style="background: linear-gradient(180deg, #020617 0%, var(--color-brand-secondary) 55%, color-mix(in srgb, var(--color-brand) 35%, #0f172a) 100%);"
        >
            <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
                @if ($logo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="" class="h-10 w-10 rounded-xl object-cover ring-1 ring-white/15">
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-sm font-bold ring-1 ring-white/10">ف</span>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">{{ $brandName }}</p>
                    <p class="truncate text-xs text-slate-300">{{ $panelLabel }}</p>
                </div>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="ناوبری پنل">
                {{ $nav }}
            </nav>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-slate-200/80 bg-white/85 px-4 py-3 backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="ui-btn-secondary min-h-11 cursor-pointer lg:hidden"
                        data-sidebar-toggle
                        aria-expanded="false"
                        aria-label="باز و بسته کردن منو"
                    >
                        منو
                    </button>
                    <p class="text-sm font-medium text-slate-700">{{ $panelLabel }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($userLabel)
                        <span class="hidden rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm text-slate-600 sm:inline">{{ $userLabel }}</span>
                    @endif
                    <form method="POST" action="{{ route($logoutRoute) }}">
                        @csrf
                        <button type="submit" class="ui-btn-secondary min-h-11 cursor-pointer">خروج</button>
                    </form>
                </div>
            </header>

            <main id="panel-main" class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <x-flash />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
