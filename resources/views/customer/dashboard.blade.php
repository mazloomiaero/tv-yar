@extends('layouts.customer')

@section('content')
    <x-page-header title="درخواست‌های من" />

    @if ($serviceRequests->isEmpty())
        <x-empty-state>درخواستی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>شماره</th>
                <th>وضعیت</th>
                <th>اقدام</th>
            </x-slot:head>
            @foreach ($serviceRequests as $serviceRequest)
                <tr>
                    <td>{{ $serviceRequest->request_number }}</td>
                    <td>{{ $serviceRequest->status?->name }}</td>
                    <td class="space-x-2 space-x-reverse">
                        @if ($serviceRequest->reception)
                            <a class="ui-link" href="{{ route('customer.service-requests.reception', $serviceRequest) }}">
                                رسید {{ $serviceRequest->reception->receipt_number }}
                            </a>
                        @endif
                        <a class="ui-link" href="{{ route('customer.service-requests.feedback.create', $serviceRequest) }}">
                            بازخورد
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif

    <h2 class="mt-8 mb-3 text-sm font-semibold text-slate-700">فاکتورهای من</h2>
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
@endsection
