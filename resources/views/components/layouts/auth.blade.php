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
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ورود' }} — {{ $productName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-brand: {{ $primary }};
            --color-brand-secondary: {{ $secondary }};
        }
    </style>
</head>
<body class="auth-shell min-h-screen text-slate-800 antialiased">
    <a href="#auth-main" class="auth-skip">رفتن به فرم ورود</a>

    <div class="auth-grid">
        <aside class="auth-brand" aria-label="معرفی سامانه">
            <div class="auth-brand-glow" aria-hidden="true"></div>
            <div class="auth-brand-grid" aria-hidden="true"></div>

            <div class="auth-brand-inner">
                <div class="auth-brand-mark">
                    @if ($logo)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}"
                            alt=""
                            class="h-14 w-14 rounded-2xl object-cover ring-1 ring-white/20"
                        >
                    @else
                        <span class="auth-logo-fallback" aria-hidden="true">ف</span>
                    @endif
                </div>

                <p class="auth-kicker">سامانه سازمانی</p>
                <h1 class="auth-brand-title">{{ $productName }}</h1>
                @if ($brandName !== $productName)
                    <p class="auth-brand-company">{{ $brandName }}</p>
                @endif
                <p class="auth-brand-lead">
                    مدیریت درخواست خدمت، ارجاع تکنسین، گزارش تعمیر، فاکتور و پرداخت در یک جریان امن و یکپارچه.
                </p>

                <ul class="auth-trust-list">
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M12 3 4.5 6.5v5.2c0 4.4 3.2 8.4 7.5 9.3 4.3-.9 7.5-4.9 7.5-9.3V6.5L12 3Z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        <span>ورود امن با نشست محافظت‌شده</span>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 7h16M4 12h10M4 17h7" />
                        </svg>
                        <span>پیگیری شفاف وضعیت خدمت</span>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="5" y="11" width="14" height="10" rx="2" />
                            <path d="M8 11V8a4 4 0 0 1 8 0v3" />
                        </svg>
                        <span>حفاظت از داده و تاریخچه عملیات</span>
                    </li>
                </ul>
            </div>
        </aside>

        <main id="auth-main" class="auth-panel">
            <div class="auth-panel-card auth-enter">
                {{ $slot }}
            </div>

            <p class="auth-footer">
                <a href="{{ url('/') }}" class="ui-link">بازگشت به صفحه اصلی</a>
            </p>
        </main>
    </div>
</body>
</html>
