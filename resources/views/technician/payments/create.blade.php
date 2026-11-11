@extends('layouts.technician')

@section('content')
    <x-page-header title="ثبت پرداخت" subtitle="فاکتور {{ $invoice->invoice_number }}" />

    <x-card class="mb-4">
        <dl class="ui-dl">
            <div>
                <dt>جمع</dt>
                <dd>{{ $invoice->total }}</dd>
            </div>
            <div>
                <dt>وضعیت</dt>
                <dd>{{ $invoice->status }}</dd>
            </div>
            <div>
                <dt>مانده</dt>
                <dd>{{ $remaining }}</dd>
            </div>
        </dl>
    </x-card>

    <form method="post" action="{{ route('technician.payments.store', $invoice) }}" enctype="multipart/form-data" class="mb-4">
        @csrf
        <x-form-shell kicker="مالی" title="ثبت پرداخت" subtitle="روش پرداخت و مبلغ را وارد کنید.">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="method" class="ui-label">روش</label>
                    <select id="method" name="method" class="ui-select">
                        @foreach ($methods as $method)
                            <option value="{{ $method }}" @selected(old('method') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="amount" class="ui-label">مبلغ</label>
                    <input id="amount" name="amount" value="{{ old('amount', $remaining) }}" class="ui-input">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="reference_number" class="ui-label">شماره مرجع</label>
                    <input id="reference_number" name="reference_number" value="{{ old('reference_number') }}" class="ui-input">
                </div>
                <div>
                    <label for="receipt_image" class="ui-label">تصویر رسید</label>
                    <input id="receipt_image" name="receipt_image" type="file" class="ui-file">
                </div>
            </div>

            <x-slot:actions>
                <x-button>ثبت پرداخت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>

    <x-card title="پرداخت‌های ثبت‌شده">
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
@endsection
