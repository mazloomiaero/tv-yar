@extends('layouts.technician')

@section('content')
    <x-page-header title="داشبورد تکنسین" subtitle="ارجاع‌های تخصیص‌یافته به شما" />

    <div class="mb-8 grid gap-3 sm:grid-cols-2">
        <div class="ui-stat">
            <p class="ui-stat-label">ارجاع‌ها</p>
            <p class="ui-stat-value">{{ $assignmentCount }}</p>
            <p class="ui-stat-key">assignments: {{ $assignmentCount }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-label">از فهرست پیشنهاد</p>
            <p class="ui-stat-value">{{ $suggestedCount }}</p>
            <p class="ui-stat-key">is_suggested true: {{ $suggestedCount }}</p>
        </div>
    </div>

    @if ($assignments->isEmpty())
        <x-empty-state>ارجاعی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>درخواست</th>
                <th>تاریخ</th>
                <th>پیشنهاد</th>
            </x-slot:head>
            @foreach ($assignments as $assignment)
                <tr>
                    <td>{{ $assignment->serviceRequest?->request_number }}</td>
                    <td>{{ $assignment->scheduled_date?->toDateString() }}</td>
                    <td>is_suggested {{ $assignment->is_suggested ? 'true' : 'false' }}</td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
