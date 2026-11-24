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

    $requestTotal = (int) ($entityCounts['service_requests'] ?? 0);
    $invoiceTotal = (int) collect($invoiceCountsByStatus)->sum();
    $paidInvoices = (int) ($invoiceCountsByStatus['paid'] ?? 0);
    $paidProgress = $invoiceTotal > 0 ? (int) round(($paidInvoices / $invoiceTotal) * 100) : 8;
    $feedbackCount = (int) ($entityCounts['feedbacks'] ?? 0);
    $assignmentTotal = (int) ($assignmentSuggestionCounts['total'] ?? 0);
    $openAssignments = $assignmentTotal;
    $todayRequests = (int) (collect($requestDailyCounts)->last()['count'] ?? 0);

    $numericScores = collect($recentFeedbacks)
        ->map(fn ($feedback) => is_numeric($feedback->score) ? (float) $feedback->score : null)
        ->filter(fn ($score) => $score !== null);
    $avgScore = $numericScores->isNotEmpty()
        ? round($numericScores->avg(), 1)
        : null;

    $palette = ['#2563eb', '#f59e0b', '#10b981', '#8b5cf6', '#94a3b8', '#0ea5e9', '#f97316'];
    $statusEntries = collect($requestCountsByStatus)->take(6);
    $statusTotal = max(1, (int) $statusEntries->sum());
    $circumference = 2 * M_PI * 42;
    $donutOffset = 0;
    $statusIndex = 0;

    $dailyMax = max(1, (int) collect($requestDailyCounts)->max('count'));
    $chartPoints = [];
    $dailyCount = max(1, count($requestDailyCounts));
    foreach ($requestDailyCounts as $index => $point) {
        $x = 40 + ($dailyCount === 1 ? 0 : ($index / ($dailyCount - 1)) * 580);
        $y = 160 - (((int) $point['count']) / $dailyMax) * 120;
        $chartPoints[] = ['x' => round($x, 1), 'y' => round($y, 1), 'label' => $point['label'], 'count' => (int) $point['count']];
    }
    $linePath = '';
    $areaPath = '';
    if ($chartPoints !== []) {
        $linePath = 'M '.$chartPoints[0]['x'].' '.$chartPoints[0]['y'];
        foreach (array_slice($chartPoints, 1) as $point) {
            $linePath .= ' L '.$point['x'].' '.$point['y'];
        }
        $areaPath = $linePath.' L '.$chartPoints[array_key_last($chartPoints)]['x'].' 180 L '.$chartPoints[0]['x'].' 180 Z';
    }
    $axisLabels = collect($chartPoints)->values()->filter(function ($point, $index) use ($chartPoints) {
        $last = count($chartPoints) - 1;
        return $index === 0 || $index === $last || $index === (int) floor($last / 2) || $index === (int) floor($last / 4) || $index === (int) floor(($last * 3) / 4);
    });
@endphp

@section('content')
    <x-dashboard.hero
        eyebrow="پنل مدیریت"
        title="داشبورد مدیریت"
        subtitle="نمای زنده عملیات خدمت: درخواست، ارجاع، فاکتور و رضایت مشتری."
    >
        <x-slot:actions>
            <x-button href="{{ route('admin.customers.search') }}">جستجو / ثبت مشتری</x-button>
            <x-button variant="secondary" href="{{ route('admin.service-requests.index') }}">همه درخواست‌ها</x-button>
            <x-button variant="secondary" href="{{ route('admin.calendar') }}">تقویم ارجاع</x-button>
        </x-slot:actions>
    </x-dashboard.hero>

    <div class="dash-metric-grid mb-5">
        <x-dashboard.metric
            label="درخواست‌های امروز"
            :value="$todayRequests"
            icon="blue"
            :progress="$todayRequests > 0 ? min(100, 30 + $todayRequests * 12) : 10"
            tone="brand"
            :href="route('admin.service-requests.index', ['created' => 'today'])"
        >
            <x-slot:hint>ثبت‌شده در تاریخ جاری</x-slot:hint>
        </x-dashboard.metric>
        <x-dashboard.metric
            label="ارجاع‌های فعال"
            :value="$openAssignments"
            icon="orange"
            :progress="$openAssignments > 0 ? min(100, 25 + $openAssignments * 8) : 10"
            bar="orange"
            tone="warn"
            :href="route('admin.service-requests.index')"
        >
            <x-slot:hint>پیشنهادی {{ $assignmentSuggestionCounts['suggested'] }} · دستی {{ $assignmentSuggestionCounts['not_suggested'] }}</x-slot:hint>
        </x-dashboard.metric>
        <x-dashboard.metric label="فاکتورهای پرداخت‌شده" :value="$paidInvoices" icon="green" :progress="$paidProgress" bar="green" tone="ok">
            <x-slot:hint>از {{ $invoiceTotal }} فاکتور</x-slot:hint>
        </x-dashboard.metric>
        <x-dashboard.metric label="میانگین رضایت" :value="$avgScore !== null ? $avgScore : '—'" icon="purple" :progress="$avgScore !== null ? min(100, (int) round(($avgScore / 5) * 100)) : 12" bar="purple" tone="soft">
            <x-slot:hint>{{ $feedbackCount }} بازخورد ثبت‌شده</x-slot:hint>
        </x-dashboard.metric>
        <x-dashboard.metric
            label="اعتبار پیامک"
            :value="$smsCredit !== null ? number_format($smsCredit) : '—'"
            icon="blue"
            :progress="$smsCredit !== null ? 70 : 12"
            tone="brand"
        >
            <x-slot:hint>
                @if ($smsCredit !== null)
                    موجودی FarazSMS
                @elseif ($smsApiConfigured)
                    دریافت اعتبار ناموفق
                @else
                    <a href="{{ route('admin.sms-settings.edit') }}" class="underline decoration-slate-300 underline-offset-2 hover:decoration-slate-500">پیکربندی API</a>
                @endif
            </x-slot:hint>
        </x-dashboard.metric>
    </div>

    <div class="dash-charts">
        <x-dashboard.section title="آمار خدمات ۳۰ روز اخیر" subtitle="روند ثبت درخواست‌ها بر اساس تاریخ ایجاد">
            <svg class="dash-chart-line" viewBox="0 0 640 220" role="img" aria-label="نمودار ثبت درخواست ۳۰ روز اخیر">
                <defs>
                    <linearGradient id="dashAreaFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#2563eb" stop-opacity=".28"/>
                        <stop offset="100%" stop-color="#2563eb" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <g stroke="#e2e8f0" stroke-width="1">
                    <line x1="40" y1="40" x2="620" y2="40"/>
                    <line x1="40" y1="80" x2="620" y2="80"/>
                    <line x1="40" y1="120" x2="620" y2="120"/>
                    <line x1="40" y1="160" x2="620" y2="160"/>
                </g>
                @if ($areaPath !== '')
                    <path d="{{ $areaPath }}" fill="url(#dashAreaFill)"/>
                    <path d="{{ $linePath }}" fill="none" stroke="#2563eb" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                    @foreach ($chartPoints as $point)
                        @if ($point['count'] > 0 || $loop->first || $loop->last)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#2563eb"/>
                        @endif
                    @endforeach
                @else
                    <text x="320" y="110" text-anchor="middle" fill="#94a3b8" font-size="14">داده‌ای برای نمایش نیست</text>
                @endif
                <g fill="#94a3b8" font-size="12">
                    @foreach ($axisLabels as $point)
                        <text x="{{ $point['x'] }}" y="205" text-anchor="middle">{{ $point['label'] }}</text>
                    @endforeach
                </g>
            </svg>
        </x-dashboard.section>

        <x-dashboard.section title="وضعیت درخواست‌ها" subtitle="توزیع فعلی بر اساس وضعیت ذخیره‌شده">
            <div class="dash-donut-wrap">
                <div class="dash-donut">
                    <svg viewBox="0 0 120 120" aria-hidden="true">
                        <circle cx="60" cy="60" r="42" fill="none" stroke="#e2e8f0" stroke-width="16"/>
                        @forelse ($statusEntries as $statusName => $count)
                            @php
                                $length = ($count / $statusTotal) * $circumference;
                                $color = $palette[$statusIndex % count($palette)];
                                $dashoffset = -$donutOffset;
                                $donutOffset += $length;
                                $statusIndex++;
                            @endphp
                            <circle
                                cx="60" cy="60" r="42" fill="none"
                                stroke="{{ $color }}" stroke-width="16"
                                stroke-dasharray="{{ round($length, 2) }} {{ round($circumference - $length, 2) }}"
                                stroke-dashoffset="{{ round($dashoffset, 2) }}"
                            />
                        @empty
                        @endforelse
                    </svg>
                    <div class="dash-donut__center">
                        <div>
                            <span class="dash-donut__value">{{ $requestTotal }}</span>
                            <span class="dash-donut__label">کل درخواست</span>
                        </div>
                    </div>
                </div>
                <ul class="dash-legend">
                    @php $statusIndex = 0; @endphp
                    @forelse ($statusEntries as $statusName => $count)
                        <li>
                            <span>
                                <i class="dash-legend__dot" style="background: {{ $palette[$statusIndex % count($palette)] }}"></i>
                                {{ $statusName }}
                            </span>
                            <strong>{{ $count }}</strong>
                        </li>
                        @php $statusIndex++; @endphp
                    @empty
                        <li><span>وضعیتی ثبت نشده است.</span><strong>0</strong></li>
                    @endforelse
                </ul>
            </div>
        </x-dashboard.section>
    </div>

    <x-dashboard.section title="زنجیره کامل خدمت" subtitle="مسیر عملیاتی از مشتری تا تأیید پرداخت">
        <div class="dash-lifecycle">
            <div class="dash-lifecycle__step">
                <div class="dash-lifecycle__index">۱</div>
                <p class="dash-lifecycle__title">ثبت مشتری و دستگاه</p>
                <p class="dash-lifecycle__hint">پایه پرونده</p>
            </div>
            <div class="dash-lifecycle__step">
                <div class="dash-lifecycle__index">۲</div>
                <p class="dash-lifecycle__title">درخواست تعمیر</p>
                <p class="dash-lifecycle__hint">شرح مشکل</p>
            </div>
            <div class="dash-lifecycle__step">
                <div class="dash-lifecycle__index">۳</div>
                <p class="dash-lifecycle__title">ارجاع تکنسین</p>
                <p class="dash-lifecycle__hint">پیشنهاد + انتخاب</p>
            </div>
            <div class="dash-lifecycle__step">
                <div class="dash-lifecycle__index">۴</div>
                <p class="dash-lifecycle__title">پذیرش / کارگاه</p>
                <p class="dash-lifecycle__hint">رسید پذیرش</p>
            </div>
            <div class="dash-lifecycle__step">
                <div class="dash-lifecycle__index">۵</div>
                <p class="dash-lifecycle__title">گزارش فنی</p>
                <p class="dash-lifecycle__hint">قطعات و تصاویر</p>
            </div>
            <div class="dash-lifecycle__step">
                <div class="dash-lifecycle__index">۶</div>
                <p class="dash-lifecycle__title">صدور فاکتور</p>
                <p class="dash-lifecycle__hint">سطرهای هزینه</p>
            </div>
            <div class="dash-lifecycle__step">
                <div class="dash-lifecycle__index">۷</div>
                <p class="dash-lifecycle__title">پرداخت و تأیید</p>
                <p class="dash-lifecycle__hint">درگاه / دستی</p>
            </div>
        </div>
    </x-dashboard.section>

    <div class="dash-bottom">
        <x-dashboard.section title="بازخورد مشتریان" subtitle="آخرین امتیازها و نظرات">
            <div class="text-3xl font-bold tracking-tight text-slate-900">
                {{ $avgScore !== null ? $avgScore : '—' }}
                <span class="text-base font-medium text-slate-500">/ ۵</span>
            </div>
            <div class="dash-stars" aria-hidden="true">★★★★★</div>
            @forelse ($recentFeedbacks->take(2) as $feedback)
                <div class="dash-quote">
                    <span class="panel-avatar">{{ mb_substr((string) ($feedback->score ?: 'ب'), 0, 1) }}</span>
                    <div>
                        <p>{{ $feedback->comment ?: 'بدون توضیح' }}</p>
                        <strong class="mt-1 block text-xs text-slate-900">امتیاز: {{ $feedback->score }} · {{ $feedback->serviceRequest?->request_number }}</strong>
                    </div>
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-500">هنوز بازخوردی ثبت نشده است.</p>
            @endforelse
        </x-dashboard.section>

        <x-dashboard.section title="فعالیت‌های اخیر" subtitle="آخرین رویدادهای عملیاتی">
            <ul class="dash-activity">
                @forelse ($recentEvents as $event)
                    <li>
                        <time>{{ $event['time'] }}</time>
                        <div>
                            <p>{{ $event['title'] }}</p>
                            <span>{{ $event['detail'] }}</span>
                        </div>
                    </li>
                @empty
                    <li>
                        <time>—</time>
                        <div>
                            <p>هنوز رویدادی ثبت نشده است.</p>
                            <span>پس از ثبت درخواست و ارجاع اینجا دیده می‌شود.</span>
                        </div>
                    </li>
                @endforelse
            </ul>
        </x-dashboard.section>

        <aside class="dash-promo">
            <p class="dash-promo__title">تی‌وی‌یار؛ همراه مطمئن تعمیر و سرویس</p>
            <p class="dash-promo__text">از ثبت درخواست تا پرداخت، همه در یک مسیر شفاف و قابل پیگیری.</p>
            <a class="dash-promo__btn" href="{{ route('admin.service-requests.index') }}">مشاهده درخواست‌ها</a>
        </aside>
    </div>

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
                <div class="dash-start-index">۴</div>
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

    <div class="grid gap-5 xl:grid-cols-2">
        <x-dashboard.section title="درخواست‌ها بر اساس منبع">
            <div class="dash-metric-grid">
                @forelse ($requestCountsBySource as $source => $count)
                    <x-dashboard.metric
                        :label="$sourceLabels[$source] ?? $source"
                        :value="$count"
                        tone="soft"
                        icon="blue"
                    />
                @empty
                    <p class="text-sm text-slate-500">هنوز درخواستی ثبت نشده است.</p>
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
                        icon="green"
                    />
                @empty
                    <p class="text-sm text-slate-500">پرداختی ثبت نشده است.</p>
                @endforelse
            </div>
        </x-dashboard.section>
    </div>
@endsection
