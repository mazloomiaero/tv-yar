@extends('layouts.accountant')

@section('content')
    <x-page-header title="پرداخت {{ $payment->reference_number }}" />

    <x-card class="mb-6">
        <dl class="ui-dl">
            <div>
                <dt>فاکتور</dt>
                <dd>
                    <a class="ui-link" href="{{ route('accountant.invoices.show', $payment->invoice) }}">{{ $payment->invoice?->invoice_number }}</a>
                </dd>
            </div>
            <div>
                <dt>مبلغ</dt>
                <dd>{{ $payment->amount }}</dd>
            </div>
            <div>
                <dt>روش</dt>
                <dd>{{ $payment->method }}</dd>
            </div>
            <div>
                <dt>وضعیت</dt>
                <dd>{{ $payment->status }}</dd>
            </div>
            <div>
                <dt>زمان پرداخت</dt>
                <dd>{{ $payment->paid_at }}</dd>
            </div>
            <div>
                <dt>ثبت‌کننده</dt>
                <dd>{{ $payment->recordedBy?->username }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt>رسید</dt>
                <dd>{{ $payment->receipt_image }}</dd>
            </div>
        </dl>
    </x-card>

    @if ($payment->transaction)
        <x-card title="تراکنش درگاه" class="mb-6">
            <dl class="ui-dl">
                <div>
                    <dt>درگاه</dt>
                    <dd>{{ $payment->transaction->gateway }}</dd>
                </div>
                <div>
                    <dt>Authority</dt>
                    <dd>{{ $payment->transaction->authority }}</dd>
                </div>
                <div>
                    <dt>وضعیت</dt>
                    <dd>{{ $payment->transaction->status }}</dd>
                </div>
            </dl>
        </x-card>
    @endif

    @if ($payment->status === 'pending_confirmation')
        <form method="post" action="{{ route('accountant.payments.confirm', $payment) }}">
            @csrf
            <x-button>تأیید پرداخت</x-button>
        </form>
    @endif
@endsection
