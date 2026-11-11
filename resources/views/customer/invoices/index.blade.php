@extends('layouts.customer')

@section('content')
    <x-page-header title="فاکتورهای من" />

    @if ($invoices->isEmpty())
        <x-empty-state>فاکتوری ثبت نشده است.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>شماره</th>
                <th>مبلغ</th>
                <th>وضعیت</th>
            </x-slot:head>
            @foreach ($invoices as $invoice)
                <tr>
                    <td>
                        <a class="ui-link" href="{{ route('customer.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                    </td>
                    <td>{{ $invoice->total }}</td>
                    <td>{{ $invoice->status }}</td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
