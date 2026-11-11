@extends('layouts.accountant')

@section('content')
    <x-page-header title="گزارش مالی" />

    <h2 class="mb-3 text-sm font-semibold text-slate-700">فاکتورها بر اساس وضعیت</h2>
    <p class="mb-3 text-xs text-slate-500">Field: invoices.status. Amount: SUM(invoices.total).</p>
    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($report['invoices_by_status'] as $status => $row)
            <div class="ui-stat">
                <p class="ui-stat-value">{{ $row['count'] }}</p>
                <p class="ui-stat-key">{{ $status }}: {{ $row['count'] }} — {{ $row['amount'] }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mb-3 text-sm font-semibold text-slate-700">پرداخت‌ها بر اساس روش</h2>
    <p class="mb-3 text-xs text-slate-500">Field: payments.method. Amount: SUM(payments.amount).</p>
    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($report['payments_by_method'] as $method => $row)
            <div class="ui-stat">
                <p class="ui-stat-value">{{ $row['count'] }}</p>
                <p class="ui-stat-key">{{ $method }}: {{ $row['count'] }} — {{ $row['amount'] }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mb-3 text-sm font-semibold text-slate-700">پرداخت‌ها بر اساس وضعیت</h2>
    <p class="mb-3 text-xs text-slate-500">Field: payments.status. Amount: SUM(payments.amount).</p>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($report['payments_by_status'] as $status => $row)
            <div class="ui-stat">
                <p class="ui-stat-value">{{ $row['count'] }}</p>
                <p class="ui-stat-key">{{ $status }}: {{ $row['count'] }} — {{ $row['amount'] }}</p>
            </div>
        @endforeach
    </div>
@endsection
