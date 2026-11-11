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
    <x-dashboard.hero
        eyebrow="پنل مدیریت"
        title="داشبورد مدیریت"
        subtitle="نمای کلی همین نصب: موجودیت‌ها، منابع درخواست، ارجاع‌ها و وضعیت مالی."
    >
        <x-slot:actions>
            <x-button href="{{ route('admin.customers.search') }}">جستجو / ثبت مشتری</x-button>
            <x-button variant="secondary" href="{{ route('admin.service-requests.index') }}">همه درخواست‌ها</x-button>
            <x-button variant="secondary" href="{{ route('admin.calendar') }}">تقویم ارجاع</x-button>
        </x-slot:actions>
    </x-dashboard.hero>

    <x-dashboard.section title="از کجا شروع کنم؟" subtitle="مسیر پیشنهادی راه‌اندازی عملیاتی">
        <div class="dash-bento">
            <a class="dash-start-card" href="{{ route('admin.staff.index') }}">
                <span class="dash-start-index">۱</span>
                <span class="min-w-0 text-sm leading-6 text-slate-600">
                    <span class="font-semibold text-slate-900">کارکنان</span>
                    را با نقش اپراتور، ناظر فنی یا حسابدار بسازید.
                </span>
            </a>
            <a class="dash-start-card" href="{{ route('admin.technicians.index') }}">
                <span class="dash-start-index">۲</span>
                <span class="min-w-0 text-sm leading-6 text-slate-600">
                    <span class="font-semibold text-slate-900">تکنسین</span>
                    را با شماره همراه (نام کاربری ورود) و رمز عبور بسازید.
                </span>
            </a>
            <a class="dash-start-card" href="{{ route('admin.customers.search') }}">
                <span class="dash-start-index">۳</span>
                <span class="min-w-0 text-sm leading-6 text-slate-600">
                    درخواست تعمیر جدید: ابتدا مشتری را پیدا یا ثبت کنید، دستگاه بگذارید، بعد درخواست.
                </span>
            </a>
            <div class="dash-start-card">
                <span class="dash-start-index">۴</span>
                <span class="min-w-0 text-sm leading-6 text-slate-600">
                    پیگیری در
                    <a class="ui-link" href="{{ route('admin.service-requests.index') }}">همه درخواست‌ها</a>
                    و زمان‌بندی در
                    <a class="ui-link" href="{{ route('admin.calendar') }}">تقویم ارجاع</a>.
                </span>
            </div>
        </div>
    </x-dashboard.section>

    <x-dashboard.section title="شمارش موجودیت‌ها" subtitle="وضعیت فعلی داده‌های اصلی">
        <div class="dash-metric-grid">
            @foreach ($entityCounts as $entity => $count)
                <x-dashboard.metric
                    :label="$entityLabels[$entity] ?? $entity"
                    :value="$count"
                    tone="brand"
                />
            @endforeach
        </div>
    </x-dashboard.section>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-dashboard.section title="درخواست‌ها بر اساس منبع">
            <div class="dash-metric-grid">
                @forelse ($requestCountsBySource as $source => $count)
                    <x-dashboard.metric
                        :label="$sourceLabels[$source] ?? $source"
                        :value="$count"
                        tone="soft"
                    />
                @empty
                    <p class="text-sm text-slate-500">هنوز درخواستی ثبت نشده است.</p>
                @endforelse
            </div>
        </x-dashboard.section>

        <x-dashboard.section title="استفاده از پیشنهاد ارجاع">
            <div class="dash-metric-grid dash-metric-grid--3">
                <x-dashboard.metric label="کل ارجاع‌ها" :value="$assignmentSuggestionCounts['total']" tone="brand" />
                <x-dashboard.metric label="از لیست پیشنهاد" :value="$assignmentSuggestionCounts['suggested']" tone="ok" />
                <x-dashboard.metric label="انتخاب دستی" :value="$assignmentSuggestionCounts['not_suggested']" tone="soft" />
            </div>
        </x-dashboard.section>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-dashboard.section title="فاکتورها بر اساس وضعیت">
            <div class="dash-metric-grid">
                @forelse ($invoiceCountsByStatus as $status => $count)
                    <x-dashboard.metric
                        :label="$invoiceLabels[$status] ?? $status"
                        :value="$count"
                        :tone="$status === 'paid' ? 'ok' : ($status === 'cancelled' ? 'warn' : 'brand')"
                    />
                @empty
                    <p class="text-sm text-slate-500">فاکتوری ثبت نشده است.</p>
                @endforelse
            </div>
        </x-dashboard.section>

        <x-dashboard.section title="پرداخت‌ها بر اساس روش">
            <div class="dash-metric-grid">
                @forelse ($paymentCountsByMethod as $method => $count)
                    <x-dashboard.metric
                        :label="$paymentLabels[$method] ?? $method"
                        :value="$count"
                        tone="brand"
                    />
                @empty
                    <p class="text-sm text-slate-500">پرداختی ثبت نشده است.</p>
                @endforelse
            </div>
        </x-dashboard.section>
    </div>
@endsection
