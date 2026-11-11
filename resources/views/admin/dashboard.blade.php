@extends('layouts.admin')

@php
    $entityLabels = [
        'customers' => 'مشتریان',
        'devices' => 'دستگاه‌ها',
        'service_requests' => 'درخواست‌ها',
        'service_assignments' => 'ارجاع‌ها',
        'service_reports' => 'گزارش‌ها',
        'device_receptions' => 'پذیرش کارگاه',
        'invoices' => 'فاکتورها',
        'payments' => 'پرداخت‌ها',
        'feedbacks' => 'بازخوردها',
        'technicians' => 'تکنسین‌ها',
        'branches' => 'شعب',
    ];
    $sourceLabels = [
        'website' => 'وب‌سایت',
        'phone' => 'تلفن',
        'walk-in' => 'حضوری',
    ];
    $invoiceLabels = [
        'pending_payment' => 'در انتظار پرداخت',
        'partially_paid' => 'پرداخت ناقص',
        'paid' => 'پرداخت‌شده',
        'cancelled' => 'لغو شده',
    ];
    $paymentLabels = [
        'gateway' => 'درگاه',
        'card_transfer' => 'کارت به کارت',
        'cash' => 'نقد',
    ];
@endphp

@section('content')
    <x-page-header title="داشبورد مدیریت" subtitle="شمارش‌های موجود در این نصب">
        <x-slot:actions>
            <x-button href="{{ route('admin.customers.search') }}">جستجو / ثبت مشتری</x-button>
            <x-button variant="secondary" href="{{ route('admin.service-requests.index') }}">همه درخواست‌ها</x-button>
            <x-button variant="secondary" href="{{ route('admin.calendar') }}">تقویم ارجاع</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card class="mb-8" title="از کجا شروع کنم؟">
        <div class="grid gap-3 sm:grid-cols-2">
            <a class="ui-start-step" href="{{ route('admin.staff.index') }}">
                <span class="ui-start-step-index">۱</span>
                <span class="min-w-0 text-sm text-slate-600">
                    <span class="ui-link">کارکنان</span>
                    را با نقش اپراتور، ناظر فنی یا حسابدار بسازید.
                </span>
            </a>
            <a class="ui-start-step" href="{{ route('admin.technicians.index') }}">
                <span class="ui-start-step-index">۲</span>
                <span class="min-w-0 text-sm text-slate-600">
                    تکنسین را از
                    <span class="ui-link">تکنسین‌ها</span>
                    با شماره همراه و رمز عبور بسازید.
                </span>
            </a>
            <a class="ui-start-step" href="{{ route('admin.customers.search') }}">
                <span class="ui-start-step-index">۳</span>
                <span class="min-w-0 text-sm text-slate-600">
                    درخواست تعمیر جدید: ابتدا
                    <span class="ui-link">مشتری</span>
                    را پیدا یا ثبت کنید، دستگاه بگذارید، بعد «درخواست جدید».
                </span>
            </a>
            <div class="ui-start-step">
                <span class="ui-start-step-index">۴</span>
                <span class="min-w-0 text-sm text-slate-600">
                    لیست همه درخواست‌ها در
                    <a class="ui-link" href="{{ route('admin.service-requests.index') }}">همه درخواست‌ها</a>
                    است. ارجاع تکنسین از همان صفحه درخواست یا
                    <a class="ui-link" href="{{ route('admin.calendar') }}">تقویم ارجاع</a>.
                </span>
            </div>
        </div>
    </x-card>

    <x-card class="mb-8">
        <h2 class="ui-section-title">شمارش موجودیت‌ها</h2>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($entityCounts as $entity => $count)
                <div class="ui-stat ui-stat-accent">
                    <p class="ui-stat-label">{{ $entityLabels[$entity] ?? $entity }}</p>
                    <p class="ui-stat-value">{{ $count }}</p>
                    <p class="ui-stat-key">{{ $entity }}: {{ $count }}</p>
                </div>
            @endforeach
        </div>
    </x-card>

    <x-card class="mb-8">
        <h2 class="ui-section-title">درخواست‌ها بر اساس منبع</h2>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($requestCountsBySource as $source => $count)
                <div class="ui-stat ui-stat-accent">
                    <p class="ui-stat-label">{{ $sourceLabels[$source] ?? $source }}</p>
                    <p class="ui-stat-value">{{ $count }}</p>
                    <p class="ui-stat-key">{{ $source }}: {{ $count }}</p>
                </div>
            @endforeach
        </div>
    </x-card>

    <x-card class="mb-8">
        <h2 class="ui-section-title">استفاده از پیشنهاد ارجاع</h2>
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="ui-stat ui-stat-accent">
                <p class="ui-stat-label">کل ارجاع‌ها</p>
                <p class="ui-stat-value">{{ $assignmentSuggestionCounts['total'] }}</p>
                <p class="ui-stat-key">total: {{ $assignmentSuggestionCounts['total'] }}</p>
            </div>
            <div class="ui-stat ui-stat-accent">
                <p class="ui-stat-label">از لیست پیشنهاد</p>
                <p class="ui-stat-value">{{ $assignmentSuggestionCounts['suggested'] }}</p>
                <p class="ui-stat-key">is_suggested true: {{ $assignmentSuggestionCounts['suggested'] }}</p>
            </div>
            <div class="ui-stat ui-stat-accent">
                <p class="ui-stat-label">انتخاب دستی</p>
                <p class="ui-stat-value">{{ $assignmentSuggestionCounts['not_suggested'] }}</p>
                <p class="ui-stat-key">is_suggested false: {{ $assignmentSuggestionCounts['not_suggested'] }}</p>
            </div>
        </div>
    </x-card>

    <x-card class="mb-8">
        <h2 class="ui-section-title">فاکتورها بر اساس وضعیت</h2>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($invoiceCountsByStatus as $status => $count)
                <div class="ui-stat ui-stat-accent">
                    <p class="ui-stat-label">{{ $invoiceLabels[$status] ?? $status }}</p>
                    <p class="ui-stat-value">{{ $count }}</p>
                    <p class="ui-stat-key">{{ $status }}: {{ $count }}</p>
                </div>
            @endforeach
        </div>
    </x-card>

    <x-card>
        <h2 class="ui-section-title">پرداخت‌ها بر اساس روش</h2>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($paymentCountsByMethod as $method => $count)
                <div class="ui-stat ui-stat-accent">
                    <p class="ui-stat-label">{{ $paymentLabels[$method] ?? $method }}</p>
                    <p class="ui-stat-value">{{ $count }}</p>
                    <p class="ui-stat-key">{{ $method }}: {{ $count }}</p>
                </div>
            @endforeach
        </div>
    </x-card>
@endsection
