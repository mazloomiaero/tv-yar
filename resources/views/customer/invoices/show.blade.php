@extends('layouts.customer')

@section('content')
    <x-page-header title="فاکتور {{ $invoice->invoice_number }}" />

    <x-card class="mb-6">
        <dl class="ui-dl">
            <div>
                <dt>مشتری</dt>
                <dd>{{ $invoice->customer_name }}</dd>
            </div>
            <div>
                <dt>همراه</dt>
                <dd>{{ $invoice->customer_mobile }}</dd>
            </div>
            <div>
                <dt>نشانی</dt>
                <dd>{{ $invoice->customer_address }}</dd>
            </div>
            <div>
                <dt>وضعیت</dt>
                <dd>{{ $invoice->status }}</dd>
            </div>
            <div>
                <dt>صدور</dt>
                <dd>{{ $invoice->issued_at }}</dd>
            </div>
        </dl>
    </x-card>

    <x-card title="سطرها" class="mb-6">
        <x-table>
            <x-slot:head>
                <th>عنوان</th>
                <th>مقدار</th>
                <th>قیمت واحد</th>
                <th>جمع</th>
            </x-slot:head>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unit_price }}</td>
                    <td>{{ $item->total }}</td>
                </tr>
            @endforeach
        </x-table>
        <dl class="ui-dl mt-4">
            <div>
                <dt>جمع جزء</dt>
                <dd>{{ $invoice->subtotal }}</dd>
            </div>
            <div>
                <dt>تخفیف</dt>
                <dd>{{ $invoice->discount }}</dd>
            </div>
            <div>
                <dt>مالیات</dt>
                <dd>{{ $invoice->tax }}</dd>
            </div>
            <div>
                <dt>جمع کل</dt>
                <dd>{{ $invoice->total }}</dd>
            </div>
        </dl>
    </x-card>

    <x-card title="پرداخت‌ها" class="mb-6">
        @if ($invoice->payments->isEmpty())
            <p class="text-sm text-slate-500">پرداختی ثبت نشده است.</p>
        @else
            <x-table>
                <x-slot:head>
                    <th>روش</th>
                    <th>مبلغ</th>
                    <th>وضعیت</th>
                    <th>مرجع</th>
                </x-slot:head>
                @foreach ($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->method }}</td>
                        <td>{{ $payment->amount }}</td>
                        <td>{{ $payment->status }}</td>
                        <td>{{ $payment->reference_number }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>

    @if (in_array($invoice->status, ['pending_payment', 'partially_paid'], true))
        <form method="post" action="{{ route('customer.invoices.payments.gateway', $invoice) }}">
            @csrf
            <x-button>پرداخت آنلاین</x-button>
        </form>
    @endif
@endsection
