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
    $primary = filled($brandCompany?->primary_color) ? $brandCompany->primary_color : '#2563eb';
    $secondary = filled($brandCompany?->secondary_color) ? $brandCompany->secondary_color : '#0b1b3a';
    $logo = $brandCompany?->logo;
    $userInitial = filled($userLabel) ? mb_substr((string) $userLabel, 0, 1) : 'ک';
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
            class="panel-sidebar fixed inset-y-0 start-0 z-40 flex w-72 translate-x-full flex-col text-slate-100 transition-transform duration-200 lg:static lg:translate-x-0"
        >
            <div class="flex items-center gap-3 border-b border-white/10 px-5 py-5">
                @if ($logo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="" class="h-11 w-11 rounded-xl object-cover ring-1 ring-white/15">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-sky-400 text-sm font-bold text-white shadow-lg shadow-blue-500/30 ring-1 ring-white/10">
                        {{ mb_substr($brandName, 0, 1) }}
                    </span>
                @endif
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-white">{{ $brandName }}</p>
                    <p class="truncate text-xs text-slate-300">{{ $productName }}</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="ناوبری پنل">
                {{ $nav }}
            </nav>

            <div class="space-y-3 border-t border-white/10 p-4">
                <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-3 py-2.5">
                    <span class="panel-avatar">{{ $userInitial }}</span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ $userLabel ?: 'کاربر سیستم' }}</p>
                        <p class="truncate text-xs text-slate-400">{{ $panelLabel }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route($logoutRoute) }}">
                    @csrf
                    <button type="submit" class="ui-btn-danger w-full min-h-11 cursor-pointer border-red-400/20 bg-red-500/15 text-red-100 hover:bg-red-500/25">
                        خروج
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col">
            <header class="panel-topbar">
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
                    <span class="panel-pill panel-pill--online">آنلاین</span>
                    <p class="hidden text-sm font-medium text-slate-700 sm:inline">{{ $panelLabel }}</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <span class="panel-pill tabular-nums">{{ now()->format('Y/m/d') }}</span>
                    @if ($userLabel)
                        <span class="panel-pill hidden sm:inline-flex">{{ $userLabel }}</span>
                    @endif
                </div>
            </header>

            <main id="panel-main" class="flex-1 px-4 py-5 sm:px-6 lg:px-8">
                <x-flash />
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
