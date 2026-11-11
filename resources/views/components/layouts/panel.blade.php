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
<body class="min-h-screen bg-slate-50">
    <div
        data-sidebar-backdrop
        class="fixed inset-0 z-30 hidden bg-slate-900/40 lg:hidden"
    ></div>

    <div class="min-h-screen lg:flex">
        <aside
            data-sidebar
            class="fixed inset-y-0 start-0 z-40 flex w-72 translate-x-full flex-col text-slate-100 transition-transform duration-200 lg:static lg:translate-x-0"
            style="background-color: var(--color-brand-secondary);"
        >
            <div class="flex items-center gap-3 border-b border-white/10 px-5 py-4">
                @if ($logo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="" class="h-9 w-9 rounded-lg object-cover">
                @else
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-sm font-bold">ف</span>
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
            <header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="ui-btn-secondary lg:hidden"
                        data-sidebar-toggle
                        aria-expanded="false"
                    >
                        منو
                    </button>
                    <p class="text-sm font-medium text-slate-700">{{ $panelLabel }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($userLabel)
                        <span class="hidden text-sm text-slate-500 sm:inline">{{ $userLabel }}</span>
                    @endif
                    <form method="POST" action="{{ route($logoutRoute) }}">
                        @csrf
                        <button type="submit" class="ui-btn-secondary">خروج</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6">
                <x-flash />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
