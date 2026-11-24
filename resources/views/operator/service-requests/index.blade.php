@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header
        title="درخواست‌های خدمت"
        :subtitle="$createdFilter === 'today' ? 'فقط درخواست‌های ثبت‌شده امروز.' : 'برای ثبت درخواست جدید ابتدا مشتری را باز کنید.'"
    >
        <x-slot:actions>
            @if ($createdFilter === 'today')
                <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('service-requests.index') }}">همه درخواست‌ها</x-button>
            @endif
            <x-button href="{{ \App\Support\OpsRoute::to('customers.search') }}">ثبت درخواست جدید</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($serviceRequests->isEmpty())
        <x-empty-state>
            @if ($createdFilter === 'today')
                امروز درخواستی ثبت نشده است.
            @else
                درخواستی ثبت نشده است. از جستجوی مشتری برای ثبت درخواست استفاده کنید.
            @endif
        </x-empty-state>
    @else
        <x-table>
            <x-slot:head>
                <th>شماره</th>
                <th>مشتری</th>
                <th>وضعیت</th>
                <th>عملیات</th>
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
                    <td>
                        @can('changeStatus', $serviceRequest)
                            @if (! $serviceRequest->isClosed() && $serviceRequest->status?->code !== \App\Support\CancelledServiceStatus::CODE)
                                <form
                                    method="POST"
                                    action="{{ \App\Support\OpsRoute::to('service-requests.cancel', $serviceRequest) }}"
                                    onsubmit="return confirm('این درخواست لغو شود؟ سابقه حذف فیزیکی نمی‌شود.')"
                                >
                                    @csrf
                                    <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-700">لغو</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-table>
    @endif
@endsection
