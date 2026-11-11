@extends(\App\Support\OpsRoute::layout())

@section('content')
    <x-page-header title="ویرایش مشتری" subtitle="{{ $customer->customer_code }}" />

    <x-card class="mb-6">
        <form method="POST" action="{{ \App\Support\OpsRoute::to('customers.update', $customer) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('operator.customers._form', [
                'customer' => $customer,
                'mobile' => $customer->mobile,
            ])
            <div class="flex gap-2">
                <x-button>ذخیره مشتری</x-button>
                <x-button variant="secondary" href="{{ \App\Support\OpsRoute::to('customers.search', array_filter(['q' => $customer->mobile])) }}">بازگشت به جستجو</x-button>
            </div>
        </form>
    </x-card>

    @can('viewAny', App\Models\Device::class)
        <x-page-header title="دستگاه‌ها">
            <x-slot:actions>
                @can('create', App\Models\Device::class)
                    <x-button href="{{ \App\Support\OpsRoute::to('customers.devices.create', $customer) }}">دستگاه جدید</x-button>
                @endcan
            </x-slot:actions>
        </x-page-header>
        @if ($customer->devices->isEmpty())
            <x-empty-state class="mb-6">دستگاهی برای این مشتری ثبت نشده است.</x-empty-state>
        @else
            <div class="mb-6">
                <x-table>
                    <x-slot:head>
                        <th>عنوان</th>
                        <th>نوع</th>
                        <th>برند</th>
                        <th></th>
                    </x-slot:head>
                    @foreach ($customer->devices as $device)
                        <tr>
                            <td>{{ $device->title }}</td>
                            <td>{{ $device->deviceType?->name }}</td>
                            <td>{{ $device->brand?->name }}</td>
                            <td class="text-left">
                                @can('update', $device)
                                    <a class="ui-link" href="{{ \App\Support\OpsRoute::to('customers.devices.edit', [$customer, $device]) }}">ویرایش</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </div>
        @endif
    @endcan

    @can('viewAny', App\Models\ServiceRequest::class)
        <x-page-header title="درخواست‌های خدمت">
            <x-slot:actions>
                @can('create', App\Models\ServiceRequest::class)
                    <x-button href="{{ \App\Support\OpsRoute::to('customers.service-requests.create', $customer) }}">درخواست جدید</x-button>
                @endcan
            </x-slot:actions>
        </x-page-header>
        @if ($customer->serviceRequests->isEmpty())
            <x-empty-state>درخواستی برای این مشتری ثبت نشده است.</x-empty-state>
        @else
            <x-table>
                <x-slot:head>
                    <th>شماره</th>
                    <th>وضعیت</th>
                </x-slot:head>
                @foreach ($customer->serviceRequests as $serviceRequest)
                    <tr>
                        <td>
                            <a class="ui-link" href="{{ \App\Support\OpsRoute::to('service-requests.show', $serviceRequest) }}">
                                {{ $serviceRequest->request_number }}
                            </a>
                        </td>
                        <td>{{ $serviceRequest->status?->name }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    @endcan
@endsection
