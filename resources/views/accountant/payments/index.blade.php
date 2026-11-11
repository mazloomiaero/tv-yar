@extends('layouts.accountant')

@section('content')
    <x-page-header title="پرداخت‌ها" />

    @if ($payments->isEmpty())
        <x-empty-state>پرداختی ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>مرجع</th>
                <th>فاکتور</th>
                <th>روش</th>
                <th>مبلغ</th>
                <th>وضعیت</th>
            </x-slot:head>
            @foreach ($payments as $payment)
                <tr>
                    <td>
                        <a class="ui-link" href="{{ route('accountant.payments.show', $payment) }}">{{ $payment->reference_number }}</a>
                    </td>
                    <td>{{ $payment->invoice?->invoice_number }}</td>
                    <td>{{ $payment->method }}</td>
                    <td>{{ $payment->amount }}</td>
                    <td>{{ $payment->status }}</td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
