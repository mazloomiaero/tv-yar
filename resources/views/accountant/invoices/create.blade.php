@extends('layouts.accountant')

@section('content')
    <x-page-header title="صدور فاکتور" subtitle="درخواست: {{ $serviceRequest->request_number }}" />

    <x-card class="mb-4">
        <dl class="ui-dl">
            <div>
                <dt>مشتری</dt>
                <dd>{{ $serviceRequest->customer?->first_name }} {{ $serviceRequest->customer?->last_name }}</dd>
            </div>
            <div>
                <dt>همراه</dt>
                <dd>{{ $serviceRequest->customer?->mobile }}</dd>
            </div>
        </dl>
        <p class="mt-3 text-xs text-slate-500">تصویر نشانی تا وقتی customer_addresses متن نشانی داشته باشد خالی است.</p>
    </x-card>

    <x-card title="قطعات گزارش (کپی به‌عنوان سطرهای فاکتور)" class="mb-4">
        @if ($preview === [])
            <p class="text-sm text-slate-500">قطعه‌ای برای فاکتور نیست.</p>
        @else
            <x-table>
                <x-slot:head>
                    <th>عنوان</th>
                    <th>مقدار</th>
                    <th>قیمت واحد</th>
                    <th>جمع</th>
                </x-slot:head>
                @foreach ($preview as $line)
                    <tr>
                        <td>{{ $line['title'] }}</td>
                        <td>{{ $line['quantity'] }}</td>
                        <td>{{ $line['unit_price'] }}</td>
                        <td>{{ $line['total'] }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
        <p class="mt-4 text-sm font-medium">جمع جزء: {{ $subtotal }}</p>
    </x-card>

    <form method="POST" action="{{ route('accountant.service-requests.invoices.store', $serviceRequest) }}">
        @csrf
        <x-form-shell kicker="مالی" title="صدور فاکتور" subtitle="تخفیف و مالیات را قبل از صدور بررسی کنید.">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="discount" class="ui-label">تخفیف</label>
                    <input id="discount" type="text" name="discount" value="{{ old('discount', '0.00') }}" class="ui-input">
                </div>
                <div>
                    <label for="tax" class="ui-label">مالیات</label>
                    <input id="tax" type="text" name="tax" value="{{ old('tax', '0.00') }}" class="ui-input">
                </div>
            </div>

            <x-slot:actions>
                <x-button>صدور فاکتور</x-button>
                <x-button variant="secondary" href="{{ route('accountant.invoices.index') }}">بازگشت</x-button>
            </x-slot:actions>
        </x-form-shell>
    </form>
@endsection
