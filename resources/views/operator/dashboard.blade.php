@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="داشبورد اپراتور" subtitle="برای ثبت تعمیر: مشتری را پیدا کنید، دستگاه بگذارید، سپس درخواست جدید ثبت کنید.">
        <x-slot:actions>
            <x-button href="{{ \App\Support\OpsRoute::to('customers.search') }}">جستجو / ثبت مشتری</x-button>
            <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('service-requests.index') }}">همه درخواست‌ها</x-button>
        </x-slot:actions>
    </x-page-header>

    <h2 class="mb-3 text-sm font-semibold text-slate-700">شمارش‌ها</h2>
    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <div class="ui-stat">
            <p class="ui-stat-label">مشتریان</p>
            <p class="ui-stat-value">{{ $customerCount }}</p>
            <p class="ui-stat-key">customers: {{ $customerCount }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-label">دستگاه‌ها</p>
            <p class="ui-stat-value">{{ $deviceCount }}</p>
            <p class="ui-stat-key">devices: {{ $deviceCount }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-label">درخواست‌ها</p>
            <p class="ui-stat-value">{{ $requestCount }}</p>
            <p class="ui-stat-key">service_requests: {{ $requestCount }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-label">ارجاع‌ها</p>
            <p class="ui-stat-value">{{ $assignmentCount }}</p>
            <p class="ui-stat-key">service_assignments: {{ $assignmentCount }}</p>
        </div>
        <div class="ui-stat">
            <p class="ui-stat-label">بازخوردها</p>
            <p class="ui-stat-value">{{ $feedbackCount }}</p>
            <p class="ui-stat-key">feedbacks: {{ $feedbackCount }}</p>
        </div>
    </div>

    <h2 class="mb-3 text-sm font-semibold text-slate-700">درخواست‌ها بر اساس منبع</h2>
    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($requestCountsBySource as $source => $count)
            <div class="ui-stat">
                <p class="ui-stat-value">{{ $count }}</p>
                <p class="ui-stat-key">{{ $source }}: {{ $count }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mb-3 text-sm font-semibold text-slate-700">استفاده از پیشنهاد ارجاع</h2>
    <div class="mb-8 grid gap-3 sm:grid-cols-3">
        <div class="ui-stat">
            <p class="ui-stat-value">{{ $assignmentSuggestionCounts['total'] }}</p>
            <p class="ui-stat-key">total: {{ $assignmentSuggestionCounts['total'] }}</p>
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
