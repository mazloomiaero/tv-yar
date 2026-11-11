@extends('layouts.customer')

@php
    $invoiceLabels = [
        'pending_payment' => 'در انتظار پرداخت',
        'partially_paid' => 'پرداخت ناقص',
        'paid' => 'پرداخت‌شده',
        'cancelled' => 'لغو شده',
    ];
@endphp

@section('content')
    <x-dashboard.hero
        eyebrow="پورتال مشتری"
        title="درخواست‌های من"
        subtitle="وضعیت خدمات، رسید پذیرش، بازخورد و فاکتورها."
    >
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('customer.profile.edit') }}">پروفایل</x-button>
            <x-button variant="secondary" href="{{ route('customer.invoices.index') }}">فاکتورها</x-button>
        </x-slot:actions>
    </x-dashboard.hero>

    <x-dashboard.section title="خدمات در جریان" subtitle="آخرین درخواست‌های ثبت‌شده برای شما">
        @if ($serviceRequests->isEmpty())
            <x-empty-state>درخواستی ثبت نشده است.</x-empty-state>
        @else
            <div class="dash-table-panel">
                <x-table>
                    <x-slot:head>
                        <th>شماره</th>
                        <th>وضعیت</th>
                        <th>اقدام</th>
                    </x-slot:head>
                    @foreach ($serviceRequests as $serviceRequest)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $serviceRequest->request_number }}</td>
                            <td><span class="ui-badge">{{ $serviceRequest->status?->name }}</span></td>
                            <td>
                                <div class="flex flex-wrap gap-3">
                                    @if ($serviceRequest->reception)
                                        <a class="ui-link" href="{{ route('customer.service-requests.reception', $serviceRequest) }}">
                                            رسید {{ $serviceRequest->reception->receipt_number }}
                                        </a>
                                    @endif
                                    <a class="ui-link" href="{{ route('customer.service-requests.feedback.create', $serviceRequest) }}">
                                        بازخورد
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </div>
        @endif
    </x-dashboard.section>

    <x-dashboard.section title="فاکتورهای من">
        @if ($invoices->isEmpty())
            <x-empty-state>فاکتوری ثبت نشده است.</x-empty-state>
        @else
            <div class="dash-table-panel">
                <x-table>
                    <x-slot:head>
                        <th>شماره</th>
                        <th>وضعیت</th>
                        <th>مبلغ</th>
                        <th></th>
                    </x-slot:head>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $invoice->invoice_number }}</td>
                            <td><span class="ui-badge">{{ $invoiceLabels[$invoice->status] ?? $invoice->status }}</span></td>
                            <td class="tabular-nums">{{ number_format((float) $invoice->total) }}</td>
                            <td>
                                <a class="ui-link" href="{{ route('customer.invoices.show', $invoice) }}">جزئیات</a>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </div>
        @endif
    </x-dashboard.section>
@endsection
