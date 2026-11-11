@extends('layouts.accountant')

@section('content')
    <x-page-header title="فاکتورها" />

    <h2 class="mb-3 text-sm font-semibold text-slate-700">صدور فاکتور</h2>
    @if ($billableRequests->isEmpty())
        <x-empty-state class="mb-8">درخواست خدمتی با قطعات گزارش وجود ندارد.</x-empty-state>
    @else
        <div class="mb-8">
            <x-table>
                <x-slot:head>
                    <th>درخواست</th>
                    <th></th>
                </x-slot:head>
                @foreach ($billableRequests as $serviceRequest)
                    <tr>
                        <td>{{ $serviceRequest->request_number }}</td>
                        <td class="text-left">
                            <a class="ui-link" href="{{ route('accountant.service-requests.invoices.create', $serviceRequest) }}">صدور فاکتور</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>
    @endif

    <h2 class="mb-3 text-sm font-semibold text-slate-700">صادر شده</h2>
    @if ($invoices->isEmpty())
        <x-empty-state>فاکتوری ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>شماره</th>
                <th>مشتری</th>
                <th>مبلغ</th>
                <th>وضعیت</th>
            </x-slot:head>
            @foreach ($invoices as $invoice)
                <tr>
                    <td>
                        <a class="ui-link" href="{{ route('accountant.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                    </td>
                    <td>{{ $invoice->customer_name }}</td>
                    <td>{{ $invoice->total }}</td>
                    <td>{{ $invoice->status }}</td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
