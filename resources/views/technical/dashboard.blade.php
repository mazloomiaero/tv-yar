@extends('layouts.technical')

@php
    $sourceLabels = [
        'website' => 'وب‌سایت',
        'phone' => 'تلفن',
        'walk-in' => 'حضوری',
    ];
@endphp

@section('content')
    <x-dashboard.hero
        eyebrow="سرپرستی فنی"
        title="داشبورد سرپرست فنی"
        subtitle="نظارت بر درخواست‌ها، ارجاع‌ها، گزارش‌ها و پذیرش کارگاه."
    />

    <x-dashboard.section title="شاخص‌های فنی">
        <div class="dash-metric-grid dash-metric-grid--3">
            <x-dashboard.metric label="درخواست‌ها" :value="$requestCount" tone="brand" icon="blue" :progress="65" />
            <x-dashboard.metric label="ارجاع‌ها" :value="$assignmentCount" tone="soft" icon="orange" :progress="50" bar="orange" />
            <x-dashboard.metric label="پذیرش کارگاه" :value="$receptionCount" tone="ok" icon="green" :progress="45" bar="green" />
            <x-dashboard.metric label="گزارش‌ها" :value="$reportCount" tone="brand" icon="blue" :progress="55" />
            <x-dashboard.metric label="ارجاع از پیشنهاد" :value="$assignmentSuggestionCounts['suggested']" tone="ok" icon="green" :progress="40" bar="green" />
            <x-dashboard.metric label="ارجاع دستی" :value="$assignmentSuggestionCounts['not_suggested']" tone="soft" icon="purple" :progress="35" bar="purple" />
        </div>
    </x-dashboard.section>

    <x-dashboard.section title="درخواست‌های خدمت" subtitle="آخرین موارد برای پیگیری کارگاه و میدان">
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
