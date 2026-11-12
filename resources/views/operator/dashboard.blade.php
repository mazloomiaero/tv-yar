@extends(\App\Support\OpsRoute::layout())

@php
    $sourceLabels = [
        'website' => 'وب‌سایت',
        'phone' => 'تلفن',
        'walk-in' => 'حضوری',
    ];
@endphp

@section('content')
    <x-dashboard.hero
        eyebrow="عملیات"
        title="داشبورد اپراتور"
        subtitle="برای ثبت تعمیر: مشتری را پیدا کنید، دستگاه بگذارید، سپس درخواست جدید ثبت کنید."
    >
        <x-slot:actions>
            <x-button href="{{ \App\Support\OpsRoute::to('customers.search') }}">جستجو / ثبت مشتری</x-button>
            <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('service-requests.index') }}">همه درخواست‌ها</x-button>
        </x-slot:actions>
    </x-dashboard.hero>

    <x-dashboard.section title="شمارش‌های عملیاتی">
        <div class="dash-metric-grid dash-metric-grid--5">
            <x-dashboard.metric label="مشتریان" :value="$customerCount" tone="brand" icon="blue" :progress="60" />
            <x-dashboard.metric label="دستگاه‌ها" :value="$deviceCount" tone="soft" icon="orange" :progress="48" bar="orange" />
            <x-dashboard.metric label="درخواست‌ها" :value="$requestCount" tone="brand" icon="blue" :progress="70" />
            <x-dashboard.metric label="ارجاع‌ها" :value="$assignmentCount" tone="ok" icon="green" :progress="55" bar="green" />
            <x-dashboard.metric label="بازخوردها" :value="$feedbackCount" tone="soft" icon="purple" :progress="40" bar="purple" />
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
                    <p class="text-sm text-slate-500">منبعی ثبت نشده است.</p>
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

    <x-dashboard.section title="آخرین درخواست‌های خدمت" subtitle="نمای سریع برای پیگیری">
        @if ($serviceRequests->isEmpty())
            <x-empty-state>درخواستی ثبت نشده است.</x-empty-state>
        @else
            <div class="dash-table-panel">
                <x-table>
                    <x-slot:head>
                        <th>شماره</th>
                        <th>منبع</th>
                        <th>وضعیت</th>
                    </x-slot:head>
                    @foreach ($serviceRequests as $serviceRequest)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $serviceRequest->request_number }}</td>
                            <td>{{ $sourceLabels[$serviceRequest->source] ?? $serviceRequest->source }}</td>
                            <td><span class="ui-badge">{{ $serviceRequest->status?->name }}</span></td>
                        </tr>
                    @endforeach
                </x-table>
            </div>
        @endif
    </x-dashboard.section>
@endsection
