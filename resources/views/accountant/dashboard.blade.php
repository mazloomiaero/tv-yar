@extends('layouts.accountant')

@php
    $invoiceLabels = [
        'pending_payment' => 'در انتظار پرداخت',
        'partially_paid' => 'پرداخت ناقص',
        'paid' => 'پرداخت‌شده',
        'cancelled' => 'لغو شده',
    ];
    $paymentMethodLabels = [
        'gateway' => 'درگاه',
        'card_transfer' => 'کارت به کارت',
        'cash' => 'نقد',
    ];
    $paymentStatusLabels = [
        'pending_confirmation' => 'در انتظار تأیید',
        'confirmed' => 'تأییدشده',
        'failed' => 'ناموفق',
        'cancelled' => 'لغو شده',
        'initiated' => 'آغازشده',
        'paid' => 'پرداخت‌شده',
    ];
@endphp

@section('content')
    <x-dashboard.hero
        eyebrow="مالی"
        title="داشبورد حسابدار"
        subtitle="وضعیت فاکتورها و پرداخت‌ها برای پیگیری وصول و تأیید دستی."
    />

    <x-dashboard.section title="فاکتورها بر اساس وضعیت">
        <div class="dash-metric-grid">
            @forelse ($invoiceCountsByStatus as $status => $count)
                <x-dashboard.metric
                    :label="$invoiceLabels[$status] ?? $status"
                    :value="$count"
                    :tone="$status === 'paid' ? 'ok' : ($status === 'cancelled' ? 'warn' : 'brand')"
                    :icon="$status === 'paid' ? 'green' : ($status === 'cancelled' ? 'orange' : 'blue')"
                    :progress="min(100, 20 + ((int) $count) * 8)"
                    :bar="$status === 'paid' ? 'green' : ($status === 'cancelled' ? 'orange' : 'blue')"
                />
            @empty
                <p class="text-sm text-slate-500">فاکتوری ثبت نشده است.</p>
            @endforelse
        </div>
    </x-dashboard.section>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-dashboard.section title="پرداخت‌ها بر اساس روش">
            <div class="dash-metric-grid">
                @forelse ($paymentCountsByMethod as $method => $count)
                    <x-dashboard.metric
                        :label="$paymentMethodLabels[$method] ?? $method"
                        :value="$count"
                        tone="soft"
                    />
                @empty
                    <p class="text-sm text-slate-500">پرداختی ثبت نشده است.</p>
                @endforelse
            </div>
        </x-dashboard.section>

        <x-dashboard.section title="پرداخت‌ها بر اساس وضعیت">
            <div class="dash-metric-grid">
                @forelse ($paymentCountsByStatus as $status => $count)
                    <x-dashboard.metric
                        :label="$paymentStatusLabels[$status] ?? $status"
                        :value="$count"
                        :tone="$status === 'confirmed' || $status === 'paid' ? 'ok' : ($status === 'pending_confirmation' ? 'warn' : 'brand')"
                    />
                @empty
                    <p class="text-sm text-slate-500">وضعیتی ثبت نشده است.</p>
                @endforelse
            </div>
        </x-dashboard.section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-dashboard.section title="آخرین فاکتورها">
            @if ($invoices->isEmpty())
                <x-empty-state>فاکتوری ثبت نشده است.</x-empty-state>
            @else
                <div class="dash-table-panel">
                    <x-table>
                        <x-slot:head>
                            <th>شماره</th>
                            <th>وضعیت</th>
                            <th>مبلغ</th>
                        </x-slot:head>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $invoice->invoice_number }}</td>
                                <td><span class="ui-badge">{{ $invoiceLabels[$invoice->status] ?? $invoice->status }}</span></td>
                                <td class="tabular-nums">{{ number_format((float) $invoice->total) }}</td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            @endif
        </x-dashboard.section>

        <x-dashboard.section title="آخرین پرداخت‌ها">
            @if ($payments->isEmpty())
                <x-empty-state>پرداختی ثبت نشده است.</x-empty-state>
            @else
                <div class="dash-table-panel">
                    <x-table>
                        <x-slot:head>
                            <th>مرجع</th>
                            <th>روش</th>
                            <th>وضعیت</th>
                            <th>مبلغ</th>
                        </x-slot:head>
                        @foreach ($payments as $payment)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $payment->reference_number }}</td>
                                <td>{{ $paymentMethodLabels[$payment->method] ?? $payment->method }}</td>
                                <td><span class="ui-badge">{{ $paymentStatusLabels[$payment->status] ?? $payment->status }}</span></td>
                                <td class="tabular-nums">{{ number_format((float) $payment->amount) }}</td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            @endif
        </x-dashboard.section>
    </div>
@endsection
