@extends('layouts.technical')

@section('content')
    <x-page-header title="داشبورد سرپرست فنی" />

    <h2 class="mb-3 text-sm font-semibold text-slate-700">شمارش‌ها</h2>
    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        <div class="ui-stat">
            <p class="ui-stat-value">{{ $requestCount }}</p>
            <p class="ui-stat-key">service_requests: {{ $requestCount }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-value">{{ $assignmentCount }}</p>
            <p class="ui-stat-key">service_assignments: {{ $assignmentCount }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-value">{{ $receptionCount }}</p>
            <p class="ui-stat-key">device_receptions: {{ $receptionCount }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-value">{{ $reportCount }}</p>
            <p class="ui-stat-key">service_reports: {{ $reportCount }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-value">{{ $assignmentSuggestionCounts['suggested'] }}</p>
            <p class="ui-stat-key">is_suggested true: {{ $assignmentSuggestionCounts['suggested'] }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-value">{{ $assignmentSuggestionCounts['not_suggested'] }}</p>
            <p class="ui-stat-key">is_suggested false: {{ $assignmentSuggestionCounts['not_suggested'] }}</p>
        </div>
    </div>

    <h2 class="mb-3 text-sm font-semibold text-slate-700">درخواست‌های خدمت</h2>
    @if ($serviceRequests->isEmpty())
        <x-empty-state>درخواستی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>شماره</th>
                <th>منبع</th>
                <th>وضعیت</th>
            </x-slot:head>
            @foreach ($serviceRequests as $serviceRequest)
                <tr>
                    <td>{{ $serviceRequest->request_number }}</td>
                    <td>{{ $serviceRequest->source }}</td>
                    <td>{{ $serviceRequest->status?->name }}</td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
