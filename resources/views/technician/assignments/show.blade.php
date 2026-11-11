@extends('layouts.technician')

@section('content')
    <x-page-header title="{{ $serviceRequest?->request_number }}" />

    <x-card class="mb-6">
        <dl class="ui-dl">
            <div>
                <dt>زمان برنامه‌ریزی</dt>
                <dd>{{ $assignment->scheduled_date?->format('Y-m-d') }} {{ substr((string) $assignment->start_time, 0, 5) }}–{{ substr((string) $assignment->end_time, 0, 5) }}</dd>
            </div>
            <div>
                <dt>مشتری</dt>
                <dd>{{ $serviceRequest?->customer?->customer_code }} — {{ $serviceRequest?->customer?->first_name }} {{ $serviceRequest?->customer?->last_name }}</dd>
            </div>
            <div>
                <dt>دستگاه</dt>
                <dd>{{ $serviceRequest?->device?->title }}</dd>
            </div>
            <div>
                <dt>نوع خدمت</dt>
                <dd>{{ $serviceRequest?->serviceType?->name }}</dd>
            </div>
            <div>
                <dt>وضعیت</dt>
                <dd>{{ $serviceRequest?->status?->name }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt>شرح مشکل</dt>
                <dd>{{ $serviceRequest?->problem_description }}</dd>
            </div>
        </dl>
    </x-card>

    @if ($customFields->isNotEmpty())
        <x-card title="ویژگی‌های درخواست" class="mb-6">
            <ul class="space-y-2 text-sm">
                @foreach ($customFields as $customField)
                    <li>{{ $customField->name }}: {{ $customFieldValues[$customField->id] ?? '' }}</li>
                @endforeach
            </ul>
        </x-card>
    @endif

    @if ($serviceRequest?->reports->isNotEmpty())
        <x-card title="گزارش‌های خدمت" class="mb-6">
            <ul class="space-y-2 text-sm">
                @foreach ($serviceRequest->reports as $report)
                    <li>
                        <a class="ui-link" href="{{ route('technician.reports.show', $report) }}">گزارش {{ $report->id }}</a>
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endif

    <p class="mb-6">
        <x-button href="{{ route('technician.reports.create', $assignment) }}">ثبت گزارش خدمت</x-button>
    </p>

    @if ($serviceRequest?->invoices->isNotEmpty())
        <x-card title="فاکتورها">
            <x-table>
                <x-slot:head>
                    <th>شماره</th>
                    <th>مبلغ</th>
                    <th>وضعیت</th>
                    <th></th>
                </x-slot:head>
                @foreach ($serviceRequest->invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->total }}</td>
                        <td>{{ $invoice->status }}</td>
                        <td class="text-left">
                            <a class="ui-link" href="{{ route('technician.payments.create', $invoice) }}">ثبت پرداخت</a>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @endif
@endsection
