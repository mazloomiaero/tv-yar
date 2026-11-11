@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="درخواست‌های خدمت" subtitle="برای ثبت درخواست جدید ابتدا مشتری را باز کنید.">
        <x-slot:actions>
            <x-button href="{{ \App\Support\OpsRoute::to('customers.search') }}">ثبت درخواست جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($serviceRequests->isEmpty())
        <x-empty-state>درخواستی ثبت نشده است. از جستجوی مشتری برای ثبت درخواست استفاده کنید.</x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>شماره</th>
                <th>مشتری</th>
                <th>وضعیت</th>
            </x-slot:head>
            @foreach ($serviceRequests as $serviceRequest)
                <tr>
                    <td>
                        <a class="ui-link" href="{{ \App\Support\OpsRoute::to('service-requests.show', $serviceRequest) }}">
                            {{ $serviceRequest->request_number }}
                        </a>
                    </td>
                    <td>{{ $serviceRequest->customer?->first_name }} {{ $serviceRequest->customer?->last_name }} ({{ $serviceRequest->customer?->customer_code }})</td>
                    <td>{{ $serviceRequest->status?->name }}</td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
