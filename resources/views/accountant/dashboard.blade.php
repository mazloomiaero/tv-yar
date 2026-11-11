@extends('layouts.accountant')

@section('content')
    <x-page-header title="داشبورد حسابدار" />

    <h2 class="mb-3 text-sm font-semibold text-slate-700">فاکتورها بر اساس وضعیت</h2>
    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($invoiceCountsByStatus as $status => $count)
            <div class="ui-stat">
                <p class="ui-stat-value">{{ $count }}</p>
                <p class="ui-stat-key">{{ $status }}: {{ $count }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mb-3 text-sm font-semibold text-slate-700">پرداخت‌ها بر اساس روش</h2>
    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($paymentCountsByMethod as $method => $count)
            <div class="ui-stat">
                <p class="ui-stat-value">{{ $count }}</p>
                <p class="ui-stat-key">{{ $method }}: {{ $count }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mb-3 text-sm font-semibold text-slate-700">پرداخت‌ها بر اساس وضعیت</h2>
    <div class="mb-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($paymentCountsByStatus as $status => $count)
            <div class="ui-stat">
                <p class="ui-stat-value">{{ $count }}</p>
                <p class="ui-stat-key">{{ $status }}: {{ $count }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <h2 class="mb-3 text-sm font-semibold text-slate-700">فاکتورها</h2>
            @if ($invoices->isEmpty())
                <x-empty-state>فاکتوری ثبت نشده است.</x-empty-state>
            @else
                <x-table>
                    <x-slot:head>
                        <th>شماره</th>
                        <th>وضعیت</th>
                        <th>مبلغ</th>
                    </x-slot:head>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->status }}</td>
                            <td>{{ $invoice->total }}</td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </div>
        <div>
            <h2 class="mb-3 text-sm font-semibold text-slate-700">پرداخت‌ها</h2>
            @if ($payments->isEmpty())
                <x-empty-state>پرداختی ثبت نشده است.</x-empty-state>
            @else
                <x-table>
                    <x-slot:head>
                        <th>مرجع</th>
                        <th>روش</th>
                        <th>وضعیت</th>
                        <th>مبلغ</th>
                    </x-slot:head>
                    @foreach ($payments as $payment)
                        <tr>
                            <td>{{ $payment->reference_number }}</td>
                            <td>{{ $payment->method }}</td>
                            <td>{{ $payment->status }}</td>
                            <td>{{ $payment->amount }}</td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </div>
    </div>
@endsection
